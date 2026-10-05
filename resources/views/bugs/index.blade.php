@extends('layouts.app')
@section('content')
<div class="top">
    <div>
        <div class="h1">🐛 バグ管理</div>
        <div class="sub"><a href="{{ route('issues.show', $issue) }}">{{ $issue->issue_key }} {{ $issue->summary }}</a></div>
    </div>
    <div class="actions">
        <span class="badge high">未対応 {{ $openCount }}</span>
        <span class="badge low">解決済 {{ $closedCount }}</span>
        @can('update', $issue)
            <a class="btn" href="{{ route('bugs.create', $issue) }}">+ バグ登録</a>
        @endcan
    </div>
</div>

<nav class="tabs">
    <a href="{{ route('issues.show', $issue) }}">案件詳細</a>
    <a href="{{ route('bugs.index', $issue) }}"><strong>🐛 バグ</strong></a>
    <a href="{{ route('features.index', $issue) }}">💡 機能要望</a>
    <a href="{{ route('work-packages.index', $issue) }}">WBS設計</a>
    <a href="{{ route('wiki.index', $issue) }}">仕様Wiki</a>
    <a href="{{ route('files.index', $issue) }}">成果物</a>
</nav>

@cannot('update', $issue)
    <div class="alert" role="status">この案件のバグ情報は閲覧のみです。</div>
@endcannot

<section class="card">
    @if($bugs->isEmpty())
        <p class="empty-state">登録されたバグはありません。</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th>タイトル</th>
                    <th style="width:90px">深刻度</th>
                    <th style="width:80px">優先度</th>
                    <th style="width:90px">ステータス</th>
                    <th style="width:110px">担当者</th>
                    <th style="width:100px">期限</th>
                    <th style="width:70px">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bugs as $bug)
                <tr>
                    <td class="muted">{{ $bug->id }}</td>
                    <td><a href="{{ route('bugs.show', [$issue, $bug]) }}" style="font-weight:600">{{ $bug->title }}</a>
                        @if($bug->reporter)<br><span class="muted" style="font-size:11px">報告: {{ $bug->reporter->name }}</span>@endif
                    </td>
                    <td>{{ $severityLabels[$bug->severity] ?? $bug->severity }}</td>
                    <td>
                        @php $p = $bug->priority; @endphp
                        <span class="badge {{ $p === 'critical' || $p === 'high' ? 'high' : ($p === 'low' ? 'low' : 'normal') }}">
                            {{ ['critical'=>'最高','high'=>'高','normal'=>'中','low'=>'低'][$p] ?? $p }}
                        </span>
                    </td>
                    <td><span class="bug-status bug-status-{{ $bug->status }}">{{ $statusLabels[$bug->status] ?? $bug->status }}</span></td>
                    <td>{{ $bug->assignee?->name ?? '未設定' }}</td>
                    <td class="{{ $bug->due_date?->isPast() && !$bug->isClosed() ? 'text-danger' : 'muted' }}">
                        {{ $bug->due_date?->format('Y-m-d') ?? '-' }}
                    </td>
                    <td><a class="btn light" href="{{ route('bugs.show', [$issue, $bug]) }}">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>


@endsection
