<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BugController extends Controller
{
    public function index(Issue $issue): View
    {
        $bugs = $issue->bugs()
            ->with(['reporter', 'assignee'])
            ->orderByRaw("FIELD(status, 'open','investigating','fixed','verified','closed')")
            ->orderByRaw("FIELD(severity, 'critical','high','medium','low')")
            ->get();

        return view('bugs.index', [
            'issue'         => $issue,
            'bugs'          => $bugs,
            'openCount'     => $bugs->whereNotIn('status', ['verified', 'closed'])->count(),
            'closedCount'   => $bugs->whereIn('status', ['verified', 'closed'])->count(),
            'statusLabels'  => $this->statusLabels(),
            'severityLabels'=> $this->severityLabels(),
        ]);
    }

    public function create(Issue $issue): View
    {
        return view('bugs.create', [
            'issue' => $issue,
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Issue $issue): RedirectResponse
    {
        $data = $this->validated($request);
        $data['issue_id']    = $issue->id;
        $data['reporter_id'] = auth()->id();

        Bug::create($data);

        return redirect()->route('bugs.index', $issue)->with('status', 'バグを登録しました。');
    }

    public function show(Issue $issue, Bug $bug): View
    {
        $this->ensureBugBelongsToIssue($issue, $bug);

        return view('bugs.show', [
            'issue'         => $issue,
            'bug'           => $bug->load(['reporter', 'assignee']),
            'users'         => User::orderBy('name')->get(),
            'statusLabels'  => $this->statusLabels(),
            'severityLabels'=> $this->severityLabels(),
        ]);
    }

    public function edit(Issue $issue, Bug $bug): View
    {
        $this->ensureBugBelongsToIssue($issue, $bug);

        return view('bugs.edit', [
            'issue' => $issue,
            'bug'   => $bug,
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Issue $issue, Bug $bug): RedirectResponse
    {
        $this->ensureBugBelongsToIssue($issue, $bug);
        $bug->update($this->validated($request));

        return redirect()->route('bugs.show', [$issue, $bug])->with('status', 'バグを更新しました。');
    }

    public function destroy(Issue $issue, Bug $bug): RedirectResponse
    {
        $this->ensureBugBelongsToIssue($issue, $bug);
        $bug->delete();

        return redirect()->route('bugs.index', $issue)->with('status', 'バグを削除しました。');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'               => ['required', 'max:200'],
            'description'         => ['nullable', 'string'],
            'severity'            => ['required', Rule::in(Bug::SEVERITIES)],
            'priority'            => ['required', Rule::in(Bug::PRIORITIES)],
            'status'              => ['required', Rule::in(Bug::STATUSES)],
            'assignee_id'         => ['nullable', 'exists:users,id'],
            'steps_to_reproduce'  => ['nullable', 'string'],
            'expected_behavior'   => ['nullable', 'string'],
            'actual_behavior'     => ['nullable', 'string'],
            'environment_info'    => ['nullable', 'string', 'max:500'],
            'due_date'            => ['nullable', 'date'],
        ]);
    }

    private function statusLabels(): array
    {
        return [
            'open'          => '未対応',
            'investigating' => '調査中',
            'fixed'         => '修正済',
            'verified'      => '確認済',
            'closed'        => 'クローズ',
        ];
    }

    private function severityLabels(): array
    {
        return [
            'critical' => '🔴 致命的',
            'high'     => '🟠 高',
            'medium'   => '🟡 中',
            'low'      => '🟢 低',
        ];
    }

    private function ensureBugBelongsToIssue(Issue $issue, Bug $bug): void
    {
        abort_unless($bug->issue_id === $issue->id, 404);
    }
}
