@extends('layouts.app')
@section('content')
@php $statusLabels = ['not_started'=>'未着手','in_progress'=>'着手','done'=>'終了','blocked'=>'サスペンド']; @endphp
<div class="top">
    <div>
        <div class="h1">WBS一覧</div>
        <div class="sub">{{ $issue->issue_key }} {{ $issue->summary }}</div>
    </div>
    <div class="actions">
        @can('update', $issue)
        <a class="btn" href="{{ route('work-packages.create', $issue) }}">ワークパッケージ追加</a>
        @endcan
        <a class="btn secondary" href="{{ route('work-packages.gantt', $issue) }}">ガント</a>
        <a class="btn light" href="{{ route('work-packages.kanban', $issue) }}">カンバン</a>
    </div>
</div>
<nav class="tabs">
    <a href="{{ route('issues.show', $issue) }}">案件詳細</a>
    <a href="{{ route('work-packages.index', $issue) }}"><strong>WBS設計</strong></a>
    <a href="{{ route('work-packages.gantt', $issue) }}">ガント</a>
    <a href="{{ route('work-packages.kanban', $issue) }}">カンバン</a>
    <a href="{{ route('wiki.index', $issue) }}">仕様Wiki</a>
    <a href="{{ route('repositories.index', $issue) }}">ソース管理</a>
    <a href="{{ route('files.index', $issue) }}">成果物</a>
</nav>

@cannot('update', $issue)
    <div class="alert" role="status">この案件のWBSは閲覧のみです。</div>
@endcannot

<section class="card">
    <div style="overflow-x:auto">
        <table class="table">
            <tr>
                <th>WBS</th><th>ワークパッケージ</th><th>親</th><th>担当</th><th>ステータス</th><th>進捗</th><th>期間</th><th>成果物</th>@can('update', $issue)<th>操作</th>@endcan
            </tr>
            @forelse($workPackages as $package)
                @php $depth = $package->hierarchyDepth; @endphp
                <tr>
                    <td><strong>{{ $package->wbs_code }}</strong></td>
                    <td>
                        @can('update', $issue)
                        <a href="{{ route('work-packages.edit', [$issue, $package]) }}"><span class="wbs-indent" style="--depth:{{ $depth }}"></span>{{ $package->name }}</a>
                        @else
                        <span><span class="wbs-indent" style="--depth:{{ $depth }}"></span>{{ $package->name }}</span>
                        @endcan
                        @if($package->description)<div class="muted">{{ $package->description }}</div>@endif
                    </td>
                    <td>{{ $package->parent?->wbs_code ?? '-' }}</td>
                    <td>{{ $package->assignee?->name ?? '未設定' }}</td>
                    <td><span class="badge">{{ $statusLabels[$package->status] ?? $package->status }}</span></td>
                    <td style="min-width:120px"><div class="progress"><span style="width:{{ $package->progress }}%"></span></div>{{ $package->progress }}%</td>
                    <td>{{ $package->start_date?->format('Y-m-d') ?? '-' }} - {{ $package->due_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ $package->deliverable ?? '-' }}</td>
                    @can('update', $issue)
                    <td>
                        <div class="actions">
                            <a class="btn light" href="{{ route('work-packages.edit', [$issue, $package]) }}">編集</a>
                            <form method="post" action="{{ route('work-packages.destroy', [$issue, $package]) }}">@csrf @method('delete')
                                <button class="btn danger" onclick="return confirm('削除しますか？')">削除</button>
                            </form>
                        </div>
                    </td>
                    @endcan
                </tr>
            @empty
                <tr><td colspan="{{ auth()->user()->can('update', $issue) ? 9 : 8 }}" class="muted">WBSはまだ登録されていません。</td></tr>
            @endforelse
        </table>
    </div>
</section>
@endsection
