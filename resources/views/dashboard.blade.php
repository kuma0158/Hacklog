@extends('layouts.app')
@section('content')
@php $statusLabels = ['not_started'=>'未着手','in_progress'=>'着手','done'=>'終了','blocked'=>'サスペンド']; @endphp
<div class="top">
    <div>
        <div class="h1">案件一覧</div>
        <div class="sub">案件 (プロジェクト相当) を選択すると WBS / ガント / カンバン / Wiki / ソース / 成果物 に遷移できます。</div>
    </div>
    @can('create', App\Models\Issue::class)
        <div class="actions">
            <a class="btn" href="{{ route('issues.create') }}">案件追加</a>
        </div>
    @endcan
</div>

<div class="grid cols-3">
    <section class="card"><h2>未着手</h2><div class="h1">{{ $issues->where('status','not_started')->count() }}</div></section>
    <section class="card"><h2>着手</h2><div class="h1">{{ $issues->where('status','in_progress')->count() }}</div></section>
    <section class="card"><h2>終了</h2><div class="h1">{{ $issues->where('status','done')->count() }}</div></section>
</div>

<section class="card section-gap">
    <h2>案件</h2>
    <table class="table">
        <tr><th>キー</th><th>案件名</th><th>権限</th><th>ステータス</th><th>優先度</th><th>担当者</th><th>期限</th><th>全体進捗</th><th>WBS</th><th>Wiki</th><th>成果物</th><th>ソース</th></tr>
        @forelse($issues as $row)
            @php $overall = $row->overallProgress(); $autoCalc = $row->hasWorkPackages(); @endphp
            <tr>
                <td><a href="{{ route('issues.show', $row) }}">{{ $row->issue_key }}</a></td>
                <td><a href="{{ route('issues.show', $row) }}">{{ $row->summary }}</a></td>
                <td>
                    @can('update', $row)
                        <span class="badge normal">編集可</span>
                    @else
                        <span class="badge">閲覧のみ</span>
                    @endcan
                </td>
                <td><span class="badge">{{ $statusLabels[$row->status] ?? $row->status }}</span></td>
                <td><span class="badge {{ $row->priority }}">{{ $row->priority }}</span></td>
                <td>{{ $row->assignee?->name ?? '-' }}</td>
                <td>{{ $row->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td class="progress-cell">
                    <div class="progress"><span style="width:{{ $overall }}%"></span></div>
                    <div class="progress-meta">
                        <span class="progress-value">{{ $overall }}%</span>
                        @if($autoCalc)
                            <span class="note-small">WBSから自動集計</span>
                        @else
                            <span class="note-small">手動値</span>
                        @endif
                    </div>
                </td>
                <td><a href="{{ route('work-packages.index', $row) }}">{{ $row->work_packages_count }}</a></td>
                <td><a href="{{ route('wiki.index', $row) }}">{{ $row->wiki_pages_count }}</a></td>
                <td><a href="{{ route('files.index', $row) }}">{{ $row->files_count }}</a></td>
                <td><a href="{{ route('repositories.index', $row) }}">{{ $row->repositories_count }}</a></td>
            </tr>
        @empty
            <tr><td colspan="12" class="muted">案件はまだありません。</td></tr>
        @endforelse
    </table>
</section>

<section class="card section-gap">
    <h2>通知</h2>
    @forelse($notifications as $notification)
        <p>
            <span class="badge">{{ $notification->kind }}</span>
            @if($notification->issue)
                <a href="{{ route('issues.show', $notification->issue) }}">{{ $notification->title }}</a>
            @else
                {{ $notification->title }}
            @endif
            <br><span class="muted">{{ $notification->created_at->diffForHumans() }}</span>
        </p>
    @empty
        <p class="muted">通知はありません。</p>
    @endforelse
</section>
@endsection
