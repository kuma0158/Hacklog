@extends('layouts.app')
@section('content')
@php $statusLabels = ['not_started'=>'未着手','in_progress'=>'着手','done'=>'終了','blocked'=>'サスペンド']; @endphp
<div class="top">
    <div>
        <div class="h1">WBSカンバン</div>
        <div class="sub">{{ $issue->issue_key }} {{ $issue->summary }}</div>
    </div>
    <div class="actions">
        <a class="btn light" href="{{ route('work-packages.gantt', $issue) }}">ガントチャート</a>
        <a class="btn light" href="{{ route('work-packages.index', $issue) }}">WBS一覧</a>
        @can('update', $issue)
        <a class="btn" href="{{ route('work-packages.create', $issue) }}">ワークパッケージ追加</a>
        @endcan
    </div>
</div>
<nav class="tabs">
    <a href="{{ route('issues.show', $issue) }}">案件詳細</a>
    <a href="{{ route('work-packages.index', $issue) }}">WBS設計</a>
    <a href="{{ route('work-packages.gantt', $issue) }}">ガント</a>
    <a href="{{ route('work-packages.kanban', $issue) }}"><strong>カンバン</strong></a>
    <a href="{{ route('wiki.index', $issue) }}">仕様Wiki</a>
    <a href="{{ route('repositories.index', $issue) }}">ソース管理</a>
    <a href="{{ route('files.index', $issue) }}">成果物</a>
</nav>

@cannot('update', $issue)
    <div class="alert" role="status">この案件のカンバンは閲覧のみです。ステータス移動は行えません。</div>
@endcannot

<section class="kanban">
    @foreach($columns as $status => $label)
        @php $items = $workPackages->get($status, collect()); @endphp
        <div class="kanban-col">
            <div class="kanban-head">
                <span>{{ $label }}</span>
                <span class="badge">{{ $items->count() }}</span>
            </div>
            @forelse($items as $package)
                <article class="kanban-card">
                    @can('update', $issue)
                    <a href="{{ route('work-packages.edit', [$issue, $package]) }}">
                        <div class="kanban-card-title">{{ $package->wbs_code }} {{ $package->name }}</div>
                    </a>
                    @else
                    <div class="kanban-card-title">{{ $package->wbs_code }} {{ $package->name }}</div>
                    @endcan
                    <div class="progress"><span style="width:{{ $package->progress }}%"></span></div>
                    <div class="kanban-meta" style="margin-top:8px">
                        <span>{{ $package->assignee?->name ?? '未設定' }}</span>
                        <span>{{ $package->progress }}%</span>
                    </div>
                    <div class="muted" style="margin-top:8px">{{ $package->start_date?->format('m/d') ?? '-' }} - {{ $package->due_date?->format('m/d') ?? '-' }}</div>
                    @can('update', $issue)
                    <form method="post" action="{{ route('work-packages.move', [$issue, $package]) }}" style="margin-top:10px">@csrf @method('patch')
                        <div class="field">
                            <label>移動先ステータス</label>
                            <select name="status">
                                @foreach($statusLabels as $key => $statusLabel)
                                    <option value="{{ $key }}" @selected($package->status === $key)>{{ $statusLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn light" style="margin-top:8px;width:100%">移動</button>
                    </form>
                    @endcan
                </article>
            @empty
                <div class="muted" style="padding:12px">該当なし</div>
            @endforelse
        </div>
    @endforeach
</section>
@endsection
