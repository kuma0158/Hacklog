<?php

namespace App\Http\Controllers;

use App\Models\FeatureRequest;
use App\Models\FeatureRequestVote;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeatureRequestController extends Controller
{
    public function index(Issue $issue): View
    {
        $features = $issue->featureRequests()
            ->with(['requester', 'assignee'])
            ->withCount('votes')
            ->orderByDesc('vote_count')
            ->orderByRaw("FIELD(status, 'proposed','reviewing','accepted','in_progress','done','rejected')")
            ->get();

        return view('features.index', [
            'issue'        => $issue,
            'features'     => $features,
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function create(Issue $issue): View
    {
        return view('features.create', [
            'issue' => $issue,
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Issue $issue): RedirectResponse
    {
        $data = $this->validated($request);
        $data['issue_id']     = $issue->id;
        $data['requester_id'] = auth()->id();

        FeatureRequest::create($data);

        return redirect()->route('features.index', $issue)->with('status', '機能要望を登録しました。');
    }

    public function show(Issue $issue, FeatureRequest $feature): View
    {
        $this->ensureFeatureBelongsToIssue($issue, $feature);
        $feature->load(['requester', 'assignee', 'votes']);
        $hasVoted = auth()->check() ? $feature->hasVotedBy(auth()->id()) : false;

        return view('features.show', [
            'issue'        => $issue,
            'feature'      => $feature,
            'users'        => User::orderBy('name')->get(),
            'statusLabels' => $this->statusLabels(),
            'hasVoted'     => $hasVoted,
        ]);
    }

    public function edit(Issue $issue, FeatureRequest $feature): View
    {
        $this->ensureFeatureBelongsToIssue($issue, $feature);

        return view('features.edit', [
            'issue'   => $issue,
            'feature' => $feature,
            'users'   => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Issue $issue, FeatureRequest $feature): RedirectResponse
    {
        $this->ensureFeatureBelongsToIssue($issue, $feature);
        $feature->update($this->validated($request));

        return redirect()->route('features.show', [$issue, $feature])->with('status', '機能要望を更新しました。');
    }

    public function destroy(Issue $issue, FeatureRequest $feature): RedirectResponse
    {
        $this->ensureFeatureBelongsToIssue($issue, $feature);
        $feature->delete();

        return redirect()->route('features.index', $issue)->with('status', '機能要望を削除しました。');
    }

    /** 投票トグル（1人1票・取り消し可） */
    public function vote(Issue $issue, FeatureRequest $feature): RedirectResponse
    {
        $this->ensureFeatureBelongsToIssue($issue, $feature);

        $userId   = auth()->id();
        $existing = FeatureRequestVote::where('user_id', $userId)
            ->where('feature_request_id', $feature->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $feature->decrement('vote_count');
            $message = '投票を取り消しました。';
        } else {
            FeatureRequestVote::create(['user_id' => $userId, 'feature_request_id' => $feature->id]);
            $feature->increment('vote_count');
            $message = '投票しました。';
        }

        return back()->with('status', $message);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'        => ['required', 'max:200'],
            'description'  => ['nullable', 'string'],
            'user_story'   => ['nullable', 'string'],
            'priority'     => ['required', Rule::in(FeatureRequest::PRIORITIES)],
            'status'       => ['required', Rule::in(FeatureRequest::STATUSES)],
            'assignee_id'  => ['nullable', 'exists:users,id'],
            'due_date'     => ['nullable', 'date'],
        ]);
    }

    private function statusLabels(): array
    {
        return [
            'proposed'    => '提案中',
            'reviewing'   => 'レビュー中',
            'accepted'    => '承認済',
            'rejected'    => '却下',
            'in_progress' => '開発中',
            'done'        => '完了',
        ];
    }

    private function ensureFeatureBelongsToIssue(Issue $issue, FeatureRequest $feature): void
    {
        abort_unless($feature->issue_id === $issue->id, 404);
    }
}
