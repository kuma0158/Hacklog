@extends('layouts.app')
@section('content')
<div class="top">
    <div>
        <div class="h1">💡 機能要望</div>
        <div class="sub"><a href="{{ route('issues.show', $issue) }}">{{ $issue->issue_key }} {{ $issue->summary }}</a></div>
    </div>
    <div class="actions">
        @can('update', $issue)
            <a class="btn" href="{{ route('features.create', $issue) }}">+ 機能要望を追加</a>
        @endcan
    </div>
</div>

<nav class="tabs">
    <a href="{{ route('issues.show', $issue) }}">案件詳細</a>
    <a href="{{ route('bugs.index', $issue) }}">🐛 バグ</a>
    <a href="{{ route('features.index', $issue) }}"><strong>💡 機能要望</strong></a>
    <a href="{{ route('work-packages.index', $issue) }}">WBS設計</a>
    <a href="{{ route('wiki.index', $issue) }}">仕様Wiki</a>
    <a href="{{ route('files.index', $issue) }}">成果物</a>
</nav>

@cannot('update', $issue)
    <div class="alert" role="status">この案件の機能要望は閲覧のみです。</div>
@endcannot

<section class="card">
    @if($features->isEmpty())
        <p class="empty-state">登録された機能要望はありません。</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center">投票</th>
                    <th>タイトル</th>
                    <th style="width:80px">優先度</th>
                    <th style="width:100px">ステータス</th>
                    <th style="width:110px">担当者</th>
                    <th style="width:100px">期限</th>
                    <th style="width:70px">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($features as $feature)
                <tr>
                    <td style="text-align:center">
                        <div style="font-size:18px;font-weight:700;color:var(--blue)">{{ $feature->vote_count }}</div>
                        <div style="font-size:10px;color:var(--muted)">票</div>
                    </td>
                    <td>
                        <a href="{{ route('features.show', [$issue, $feature]) }}" style="font-weight:600">{{ $feature->title }}</a>
                        @if($feature->requester)<br><span class="muted" style="font-size:11px">要望者: {{ $feature->requester->name }}</span>@endif
                    </td>
                    <td>
                        @php $p = $feature->priority; @endphp
                        <span class="badge {{ $p === 'critical' || $p === 'high' ? 'high' : ($p === 'low' ? 'low' : 'normal') }}">
                            {{ ['critical'=>'最高','high'=>'高','normal'=>'中','low'=>'低'][$p] ?? $p }}
                        </span>
                    </td>
                    <td><span class="feat-status feat-status-{{ $feature->status }}">{{ $statusLabels[$feature->status] ?? $feature->status }}</span></td>
                    <td>{{ $feature->assignee?->name ?? '未設定' }}</td>
                    <td class="muted">{{ $feature->due_date?->format('Y-m-d') ?? '-' }}</td>
                    <td><a class="btn light" href="{{ route('features.show', [$issue, $feature]) }}">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>


@endsection
