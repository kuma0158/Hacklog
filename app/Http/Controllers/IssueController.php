<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\IssueComment;
use App\Models\NotificationItem;
use App\Models\User;
use App\Models\WorkPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IssueController extends Controller
{
    private const STATUSES   = ['not_started', 'in_progress', 'done', 'blocked'];
    private const PRIORITIES = ['critical', 'high', 'normal', 'low'];

    public function index(Request $request): View
    {
        $query = Issue::with(['assignee', 'milestone'])
            ->withCount('workPackages')
            ->withAvg(['workPackages' => fn ($q) => $q->whereNull('parent_id')], 'progress')
            ->withCount(['bugs', 'featureRequests'])
            ->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $showArchived = $request->boolean('archived');
        $showArchived ? $query->archived() : $query->active();

        $issues = $query->paginate(20)->withQueryString();

        return view('issues.index', [
            'issues'        => $issues,
            'statuses'      => self::STATUSES,
            'currentStatus' => $request->query('status'),
            'showArchived'  => $showArchived,
        ]);
    }

    public function create(): View
    {
        return view('issues.create', [
            'users'      => User::orderBy('name')->get(),
            'milestones' => collect(),
            'priorities' => self::PRIORITIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, withMilestone: false);
        $data['issue_key'] = 'ISSUE-' . ((int) Issue::max('id') + 1);
        $issue = Issue::create($data);

        NotificationItem::create([
            'issue_id' => $issue->id,
            'title'    => '案件が追加されました: ' . $issue->issue_key,
            'body'     => Str::limit($issue->summary, 120),
            'kind'     => 'issue',
        ]);

        return redirect()->route('issues.show', $issue)->with('status', '案件を作成しました。');
    }

    public function show(Issue $issue): View
    {
        $issue->load([
            'assignee',
            'milestone',
            'comments.user',
            'workPackages' => fn ($q) => $q->with('assignee'),
        ]);
        $issue->setRelation(
            'workPackages',
            WorkPackage::inHierarchyOrder($issue->workPackages),
        );

        $bugCounts     = $issue->bugs()->selectRaw('status, count(*) as cnt')->groupBy('status')->pluck('cnt', 'status');
        $featureCounts = $issue->featureRequests()->selectRaw('status, count(*) as cnt')->groupBy('status')->pluck('cnt', 'status');

        return view('issues.show', [
            'issue'         => $issue,
            'users'         => User::orderBy('name')->get(),
            'milestones'    => $issue->subMilestones()->orderBy('release_date')->get(),
            'priorities'    => self::PRIORITIES,
            'bugCounts'     => $bugCounts,
            'featureCounts' => $featureCounts,
        ]);
    }

    public function update(Request $request, Issue $issue): RedirectResponse
    {
        $issue->update($this->validated($request, withMilestone: true, issue: $issue));

        NotificationItem::create([
            'issue_id' => $issue->id,
            'title'    => '案件が更新されました: ' . $issue->issue_key,
            'body'     => $issue->summary,
            'kind'     => 'issue',
        ]);

        return back()->with('status', '案件を更新しました。');
    }

    public function comment(Request $request, Issue $issue): RedirectResponse
    {
        IssueComment::create([
            'issue_id' => $issue->id,
            'user_id'  => auth()->id(),
            'body'     => $request->validate(['body' => ['required']])['body'],
        ]);

        NotificationItem::create([
            'issue_id' => $issue->id,
            'title'    => 'コメントが追加されました: ' . $issue->issue_key,
            'kind'     => 'comment',
        ]);

        return back()->with('status', 'コメントを追加しました。');
    }

    public function archive(Issue $issue): RedirectResponse
    {
        $issue->forceFill(['archived_at' => now()])->save();

        NotificationItem::create([
            'issue_id' => $issue->id,
            'title'    => '案件がアーカイブされました: ' . $issue->issue_key,
            'body'     => $issue->summary,
            'kind'     => 'issue',
        ]);

        return back()->with('status', '案件「' . $issue->issue_key . '」をアーカイブしました。');
    }

    public function unarchive(Issue $issue): RedirectResponse
    {
        $issue->forceFill(['archived_at' => null])->save();

        return back()->with('status', '案件「' . $issue->issue_key . '」のアーカイブを解除しました。');
    }

    public function react(Issue $issue, string $type): RedirectResponse
    {
        if ($type === 'watch') {
            $issue->increment('watch_count');
        }
        if ($type === 'star') {
            $issue->increment('star_count');
        }

        return back();
    }

    private function validated(Request $request, bool $withMilestone, ?Issue $issue = null): array
    {
        $rules = [
            'summary'     => ['required', 'max:200'],
            'description' => ['nullable'],
            'type'        => ['nullable', 'max:40'],
            'priority'    => ['required', Rule::in(self::PRIORITIES)],
            'status'      => ['required', Rule::in(self::STATUSES)],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'start_date'  => ['nullable', 'date'],
            'due_date'    => ['nullable', 'date'],
            'progress'    => ['nullable', 'integer', 'min:0', 'max:100'],
        ];

        if ($withMilestone) {
            $rules['milestone_id'] = [
                'nullable',
                Rule::exists('milestones', 'id')->where('issue_id', $issue?->id),
            ];
        }

        return $request->validate($rules);
    }
}
