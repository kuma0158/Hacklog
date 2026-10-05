<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\User;
use App\Models\WorkPackage;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkPackageController extends Controller
{
    private const STATUSES = ['not_started', 'in_progress', 'done', 'blocked'];

    public function index(Issue $issue): View
    {
        $workPackages = WorkPackage::inHierarchyOrder(
            $issue->workPackages()->with(['assignee', 'parent'])->get(),
        );

        return view('work-packages.index', [
            'issue' => $issue,
            'workPackages' => $workPackages,
        ]);
    }

    public function create(Issue $issue): View
    {
        return view('work-packages.create', [
            'issue' => $issue,
            'package' => null,
            'workPackages' => WorkPackage::inHierarchyOrder($issue->workPackages()->get()),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Issue $issue): RedirectResponse
    {
        $data = $this->validated($request, $issue);
        $data['issue_id'] = $issue->id;
        $data['sort_order'] = $this->nextSortOrder(
            $issue,
            isset($data['parent_id']) ? (int) $data['parent_id'] : null,
        );

        $workPackage = WorkPackage::create($data);
        $workPackage->cascadeProgressUp();
        $workPackage->cascadeStatusUp();
        $workPackage->cascadeDatesUp();

        return redirect()->route('work-packages.index', $issue)->with('status', 'WBSワークパッケージを追加しました。');
    }

    public function edit(Issue $issue, WorkPackage $workPackage): View
    {
        abort_unless($workPackage->issue_id === $issue->id, 404);
        $unavailableParentIds = [$workPackage->id, ...$workPackage->descendantIds()];

        return view('work-packages.edit', [
            'issue' => $issue,
            'package' => $workPackage,
            'workPackages' => WorkPackage::inHierarchyOrder(
                $issue->workPackages()->whereNotIn('id', $unavailableParentIds)->get(),
            ),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Issue $issue, WorkPackage $workPackage): RedirectResponse
    {
        abort_unless($workPackage->issue_id === $issue->id, 404);

        $oldParentId = $workPackage->parent_id === null ? null : (int) $workPackage->parent_id;
        $oldStart = $workPackage->start_date;
        $oldDue = $workPackage->due_date;
        $data = $this->validated($request, $issue, $workPackage);
        $newParentId = array_key_exists('parent_id', $data)
            ? ($data['parent_id'] === null ? null : (int) $data['parent_id'])
            : $oldParentId;

        if ($newParentId !== $oldParentId) {
            $data['sort_order'] = $this->nextSortOrder($issue, $newParentId);
        }

        $workPackage->update($data);

        // 開始日・終了日が同じ日数だけ動いた場合（=伸縮ではなく「移動」）は、
        // 相対的な位置関係を保つため子孫タスクも同じ日数だけ移動させる
        $this->shiftDescendantsIfMoved($workPackage, $oldStart, $oldDue);

        // 親WPの場合は進捗と期間を子から再計算する（フォームの手動入力値は無視）
        $workPackage->recomputeFromChildren();
        $workPackage->recomputeDatesFromChildren();
        // ステータスは昇格のみ。未着手のまま保存しても、着手済みの子がいれば着手に戻す。
        $workPackage->recomputeStatusFromChildren();

        // 親を付け替えた場合は元の親側も子集合が変わったので再計算（子が減った＝子の変更）
        if ($oldParentId !== $newParentId && $oldParentId) {
            $oldParent = WorkPackage::find($oldParentId);
            if ($oldParent) {
                $oldParent->recomputeFromChildren();
                $oldParent->recomputeDatesFromChildren();
                $oldParent->recomputeStatusFromChildren();
                $oldParent->cascadeProgressUp();
                $oldParent->cascadeStatusUp();
                $oldParent->cascadeDatesUp();
            }
        }

        $workPackage->cascadeProgressUp();
        $workPackage->cascadeStatusUp();
        $workPackage->cascadeDatesUp();

        return redirect()->route('work-packages.index', $issue)->with('status', 'WBSワークパッケージを更新しました。');
    }

    public function move(Request $request, Issue $issue, WorkPackage $workPackage): RedirectResponse
    {
        abort_unless($workPackage->issue_id === $issue->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        $workPackage->update([
            'status' => $data['status'],
            'progress' => $data['status'] === 'done' ? 100 : $workPackage->progress,
        ]);

        // 親WPの場合は子の平均で再計算（done で 100% にした分を上書き）
        $workPackage->recomputeFromChildren();
        $workPackage->cascadeProgressUp();
        // カンバンで子を着手に動かしたら、祖先の未着手タスクも着手へ引き上げる
        $workPackage->cascadeStatusUp();

        return back()->with('status', 'WBSステータスを更新しました。ガントチャートにも反映されています。');
    }

    public function reschedule(Request $request, Issue $issue, WorkPackage $workPackage): JsonResponse
    {
        abort_unless($workPackage->issue_id === $issue->id, 404);

        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $schedule = DB::transaction(function () use ($workPackage, $data): array {
            $workPackage->refresh();
            $oldStart = $workPackage->start_date;
            $oldDue = $workPackage->due_date;

            // A summary bar is derived from its children. Moving the whole bar is
            // supported (and shifts the subtree), but stretching just one edge
            // would immediately be overwritten by the child-date aggregation.
            if ($workPackage->hasChildren()
                && ! $this->isWholeScheduleMove($oldStart, $oldDue, $data['start_date'], $data['due_date'])) {
                throw ValidationException::withMessages([
                    'schedule' => '親タスクの期間は子タスクから自動集計されます。親タスクはバー中央をドラッグして移動してください。',
                ]);
            }

            $workPackage->update($data);
            $this->shiftDescendantsIfMoved($workPackage, $oldStart, $oldDue);

            $workPackage->recomputeDatesFromChildren();
            $workPackage->cascadeDatesUp();

            if (! $workPackage->start_date || ! $workPackage->due_date) {
                throw ValidationException::withMessages([
                    'schedule' => '子タスクの日程が未設定のため、親タスクを移動できません。先に子タスクの日程を設定してください。',
                ]);
            }

            return [
                'id' => $workPackage->id,
                'start_date' => $workPackage->start_date->format('Y-m-d'),
                'due_date' => $workPackage->due_date->format('Y-m-d'),
            ];
        });

        return response()->json($schedule);
    }

    public function destroy(Issue $issue, WorkPackage $workPackage): RedirectResponse
    {
        abort_unless($workPackage->issue_id === $issue->id, 404);

        $parent = $workPackage->parent;
        $workPackage->delete();

        // 親側は子集合が変わったので再計算（子WP が nullOnDelete で top-level 化されるケースも含む）
        if ($parent) {
            $parent->refresh();
            $parent->recomputeFromChildren();
            $parent->recomputeDatesFromChildren();
            $parent->cascadeProgressUp();
            $parent->cascadeDatesUp();
        }

        return back()->with('status', 'WBSワークパッケージを削除しました。');
    }

    public function gantt(Request $request, Issue $issue): View
    {
        $packages = WorkPackage::inHierarchyOrder(
            $issue->workPackages()->with('assignee')->withCount('children')->get(),
        );

        if ($request->filled('status')) {
            $packages = $packages
                ->where('status', (string) $request->string('status'))
                ->values();
        }

        if ($request->filled('assignee_id')) {
            $packages = $packages
                ->where('assignee_id', $request->integer('assignee_id'))
                ->values();
        }

        if ($request->filled('from') || $request->filled('to')) {
            $start = $request->filled('from') ? now()->parse($request->string('from')) : now()->startOfMonth();
            $end = $request->filled('to') ? now()->parse($request->string('to')) : $start->copy()->addMonth();
        } else {
            // デフォルトは当月1ヵ月分。ただしWPの実際の期間がそれを超える場合は表示範囲を広げる。
            // 最上位の親タスクが複数（別々の期間に散らばっている場合を含む）あっても、
            // 全WPの開始日〜終了日の最小〜最大を見るので、どのタスクも表示範囲から漏れない。
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();

            $packageStarts = $packages->pluck('start_date')->filter();
            $packageDues = $packages->pluck('due_date')->filter();

            if ($packageStarts->isNotEmpty()) {
                $start = $start->min($packageStarts->min());
            }
            if ($packageDues->isNotEmpty()) {
                $end = $end->max($packageDues->max());
            }
        }

        if ($end->lt($start)) {
            $end = $start->copy()->addMonth();
        }

        return view('work-packages.gantt', [
            'issue' => $issue,
            'workPackages' => $packages,
            'chartStart' => $start,
            'chartEnd' => $end,
            'days' => collect(CarbonPeriod::create($start, $end)),
            'totalDays' => max(1, $start->diffInDays($end) + 1),
            'users' => User::orderBy('name')->get(),
            'filters' => $request->only(['status', 'assignee_id', 'from', 'to']),
        ]);
    }

    public function kanban(Issue $issue): View
    {
        return view('work-packages.kanban', [
            'issue' => $issue,
            'columns' => [
                'not_started' => '未着手',
                'in_progress' => '着手',
                'done' => '終了',
                'blocked' => 'サスペンド',
            ],
            'workPackages' => $issue->workPackages()
                ->with('assignee')
                ->orderBy('sort_order')
                ->orderBy('wbs_code')
                ->get()
                ->groupBy('status'),
        ]);
    }

    private function validated(Request $request, Issue $issue, ?WorkPackage $workPackage = null): array
    {
        $unavailableParentIds = $workPackage
            ? [$workPackage->id, ...$workPackage->descendantIds()]
            : [];
        $parentRules = [
            'nullable',
            Rule::exists('work_packages', 'id')->where('issue_id', $issue->id),
        ];

        if ($unavailableParentIds !== []) {
            $parentRules[] = Rule::notIn($unavailableParentIds);
        }

        return $request->validate([
            'parent_id' => $parentRules,
            'assignee_id' => ['nullable', 'exists:users,id'],
            'wbs_code' => [
                'required',
                'max:40',
                Rule::unique('work_packages', 'wbs_code')
                    ->where('issue_id', $issue->id)
                    ->ignore($workPackage),
            ],
            'name' => ['required', 'max:200'],
            'description' => ['nullable'],
            'deliverable' => ['nullable', 'max:255'],
            'status' => ['required', Rule::in(self::STATUSES)],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
        ]);
    }

    /** Assign the next display position within one sibling group. */
    private function nextSortOrder(Issue $issue, ?int $parentId): int
    {
        $siblings = $issue->workPackages();

        $parentId === null
            ? $siblings->whereNull('parent_id')
            : $siblings->where('parent_id', $parentId);

        return (int) $siblings->max('sort_order') + 1;
    }

    private function isWholeScheduleMove(
        ?Carbon $oldStart,
        ?Carbon $oldDue,
        string $newStart,
        string $newDue,
    ): bool {
        if (! $oldStart || ! $oldDue) {
            return false;
        }

        $startShift = $oldStart->diffInDays(Carbon::parse($newStart), false);
        $dueShift = $oldDue->diffInDays(Carbon::parse($newDue), false);

        return $startShift === $dueShift;
    }

    /**
     * If both start_date and due_date shifted by the same number of days
     * (a drag/edit that moved the whole bar rather than stretching one end),
     * shift the WP's descendant subtree by that same delta.
     */
    private function shiftDescendantsIfMoved(WorkPackage $workPackage, ?Carbon $oldStart, ?Carbon $oldDue): void
    {
        if (! $oldStart || ! $oldDue || ! $workPackage->start_date || ! $workPackage->due_date) {
            return;
        }

        $startDelta = (int) round(($workPackage->start_date->timestamp - $oldStart->timestamp) / 86400);
        $dueDelta = (int) round(($workPackage->due_date->timestamp - $oldDue->timestamp) / 86400);

        if ($startDelta !== 0 && $startDelta === $dueDelta) {
            $workPackage->shiftDescendantDates($startDelta);
        }
    }
}
