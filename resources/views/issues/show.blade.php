@extends('layouts.app')
@section('content')
@php
    $statusLabels = ['not_started' => '未着手', 'in_progress' => '着手', 'done' => '終了', 'blocked' => 'サスペンド'];
    $overall  = $issue->overallProgress();
    $hasWps   = $issue->hasWorkPackages();

    $openBugs    = ($bugCounts['open'] ?? 0) + ($bugCounts['investigating'] ?? 0);
    $totalBugs   = $bugCounts->sum();
    $totalFeatures = $featureCounts->sum();
    $pendingFeatures = ($featureCounts['proposed'] ?? 0) + ($featureCounts['reviewing'] ?? 0);
@endphp
<div class="top">
    <div>
        <div class="h1">{{ $issue->issue_key }} {{ $issue->summary }}</div>
        <div class="sub"><a href="{{ route('issues.index') }}">案件一覧</a></div>
    </div>
    @can('update', $issue)
        <div class="actions">
            <form method="post" action="{{ route('issues.react', [$issue, 'watch']) }}">@csrf<button class="btn light">ウォッチ {{ $issue->watch_count }}</button></form>
            <form method="post" action="{{ route('issues.react', [$issue, 'star']) }}">@csrf<button class="btn light">⭐ {{ $issue->star_count }}</button></form>
        </div>
    @endcan
</div>

<nav class="tabs">
    <a href="{{ route('issues.show', $issue) }}"><strong>案件詳細</strong></a>
    <a href="{{ route('bugs.index', $issue) }}">バグ @if($openBugs > 0)<span class="tab-count">{{ $openBugs }}</span>@endif</a>
    <a href="{{ route('features.index', $issue) }}">機能要望 @if($totalFeatures > 0)<span class="tab-count">{{ $totalFeatures }}</span>@endif</a>
    <a href="{{ route('work-packages.index', $issue) }}">WBS設計</a>
    <a href="{{ route('work-packages.gantt', $issue) }}">ガントチャート</a>
    <a href="{{ route('work-packages.kanban', $issue) }}">カンバン</a>
    <a href="{{ route('wiki.index', $issue) }}">仕様Wiki</a>
    <a href="{{ route('repositories.index', $issue) }}">ソース管理</a>
    <a href="{{ route('files.index', $issue) }}">成果物</a>
</nav>

@cannot('update', $issue)
    <div class="alert" role="status">この案件は閲覧のみです。編集操作は管理者または許可されたユーザーのみ行えます。</div>
@endcannot

{{-- ── バグ・機能要望サマリ（コンパクト） ── --}}
<div class="issue-related-bar" role="group" aria-label="バグと機能要望のサマリ">
    <div class="issue-related-group issue-related-group-bugs">
        <span class="issue-related-label">バグ</span>
        <a class="issue-chip" href="{{ route('bugs.index', $issue) }}" title="バグ一覧">
            <span class="issue-chip-stat">
                <strong class="@if($openBugs > 0) is-alert @endif">{{ $openBugs }}</strong>
                <span class="issue-chip-muted">未対応</span>
            </span>
            <span class="issue-chip-sep">/</span>
            <span class="issue-chip-stat">
                <strong>{{ $totalBugs }}</strong>
                <span class="issue-chip-muted">件</span>
            </span>
        </a>
        @can('update', $issue)
            <a class="issue-chip-action" href="{{ route('bugs.create', $issue) }}" title="バグを登録" aria-label="バグを登録">+</a>
        @endcan
    </div>

    <div class="issue-related-group issue-related-group-features">
        <span class="issue-related-label">機能要望</span>
        <a class="issue-chip" href="{{ route('features.index', $issue) }}" title="機能要望一覧">
            <span class="issue-chip-stat">
                <strong class="@if($pendingFeatures > 0) is-info @endif">{{ $pendingFeatures }}</strong>
                <span class="issue-chip-muted">提案中</span>
            </span>
            <span class="issue-chip-sep">/</span>
            <span class="issue-chip-stat">
                <strong>{{ $totalFeatures }}</strong>
                <span class="issue-chip-muted">件</span>
            </span>
        </a>
        @can('update', $issue)
            <a class="issue-chip-action" href="{{ route('features.create', $issue) }}" title="機能要望を追加" aria-label="機能要望を追加">+</a>
        @endcan
    </div>
</div>

{{-- ── 進捗セクション ── --}}
<section class="card section-block">
    <div class="progress-summary">
        <div>
            <h2>案件全体の進捗</h2>
            <div class="note-small">
                @if($hasWps)
                    @php $rootCount = $issue->workPackages->whereNull('parent_id')->count(); @endphp
                    ルートWBS {{ $rootCount }} 件 (子は親で集計済) を平均 ・ 全 {{ $issue->workPackages->count() }} タスク
                @else
                    WBS 未登録のため案件の手動入力値を表示
                @endif
            </div>
        </div>
        <div class="progress-total">{{ $overall }}%</div>
    </div>
    <div class="progress progress-large"><span style="width:{{ $overall }}%"></span></div>

    @if($hasWps)
        <h3>個別タスクの進捗</h3>
        <table class="table">
            <tr><th style="width:90px">WBS</th><th>タスク</th><th style="width:120px">担当</th><th style="width:90px">状態</th><th style="width:220px">進捗</th>@can('update', $issue)<th style="width:80px">操作</th>@endcan</tr>
            @foreach($issue->workPackages as $package)
                @php $depth = $package->hierarchyDepth; @endphp
                <tr>
                    <td><strong>{{ $package->wbs_code }}</strong></td>
                    <td><span class="wbs-indent" style="--depth:{{ $depth }}"></span>{{ $package->name }}</td>
                    <td>{{ $package->assignee?->name ?? '未設定' }}</td>
                    <td><span class="status-pill status-{{ $package->status }}">{{ $statusLabels[$package->status] ?? $package->status }}</span></td>
                    <td>
                        <div class="progress-line">
                            <div class="progress"><span style="width:{{ $package->progress }}%"></span></div>
                            <span class="progress-value">{{ $package->progress }}%</span>
                        </div>
                    </td>
                    @can('update', $issue)
                        <td><a class="btn light" href="{{ route('work-packages.edit', [$issue, $package]) }}">編集</a></td>
                    @endcan
                </tr>
            @endforeach
        </table>
        <div class="actions section-gap">
            @can('update', $issue)
                <a class="btn" href="{{ route('work-packages.create', $issue) }}">WBSタスク追加</a>
            @endcan
            <a class="btn light" href="{{ route('work-packages.index', $issue) }}">WBS一覧へ</a>
        </div>
    @else
        <p class="muted" style="margin:18px 0 8px">WBSタスクを追加すると、各タスクの進捗から案件全体の進捗が自動で算出されます。</p>
        @can('update', $issue)
            <a class="btn" href="{{ route('work-packages.create', $issue) }}">最初のWBSタスクを追加</a>
        @endcan
    @endif
</section>

{{-- ── 編集 & コメント ── --}}
<div class="grid @can('update', $issue) cols-2 @endcan">
    @can('update', $issue)
        <section class="card">
            <h2>案件編集</h2>
            <form method="post" action="{{ route('issues.update', $issue) }}" class="form-grid">@csrf @method('put')
                @include('issues.form', ['issue' => $issue])
                <div><button class="btn">更新</button></div>
            </form>
        </section>
    @endcan
    <section class="card">
        <h2>コメント</h2>
        @foreach($issue->comments as $comment)
            <div class="comment"><strong>{{ $comment->user?->name ?? 'System' }}</strong><br>{{ $comment->body }}<br><span class="muted">{{ $comment->created_at->format('Y-m-d H:i') }}</span></div>
        @endforeach
        @can('update', $issue)
            <form method="post" action="{{ route('issues.comments.store', $issue) }}">@csrf
                <div class="field"><label>コメント追加</label><textarea name="body"></textarea></div>
                <button class="btn section-gap">投稿</button>
            </form>
        @endcan
    </section>
</div>
@endsection
