@extends('layouts.app')
@section('content')
@php
    $statusLabels = ['not_started' => '未着手', 'in_progress' => '着手', 'done' => '終了', 'blocked' => 'サスペンド'];
@endphp
<div class="top">
    <div>
        <div class="h1">案件一覧</div>
        @if($showArchived)<div class="sub">アーカイブ済みの案件を表示しています</div>@endif
    </div>
    @can('create', App\Models\Issue::class)
        <div class="actions"><a class="btn" href="{{ route('issues.create') }}">+ 案件追加</a></div>
    @endcan
</div>

{{-- ── フィルター ── --}}
<section class="card" style="margin-bottom:16px">
    <form method="get" class="actions" style="flex-wrap:wrap;gap:10px">
        @if($showArchived)<input type="hidden" name="archived" value="1">@endif
        <select name="status" onchange="this.form.submit()" style="width:auto">
            <option value="">すべてのステータス</option>
            @foreach($statuses as $s)
                <option value="{{ $s }}" @selected($currentStatus === $s)>{{ $statusLabels[$s] }}</option>
            @endforeach
        </select>
        @if($currentStatus)
            <a class="btn light" href="{{ route('issues.index', $showArchived ? ['archived' => 1] : []) }}">クリア</a>
        @endif
        <a class="btn light" href="{{ route('issues.index', array_filter(['status' => $currentStatus, 'archived' => $showArchived ? null : 1])) }}">
            {{ $showArchived ? '通常の一覧に戻る' : 'アーカイブ済みを表示' }}
        </a>
        <span class="muted" style="margin-left:auto;font-size:13px">{{ $issues->total() }} 件</span>
    </form>
</section>

{{-- ── 一覧テーブル ── --}}
<section class="card">
    @if($issues->isEmpty())
        <p class="empty-state">条件に一致する案件がありません。</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th style="width:110px">キー</th>
                    <th>案件名</th>
                    <th style="width:80px">優先度</th>
                    <th style="width:90px">ステータス</th>
                    <th style="width:100px">担当者</th>
                    <th style="width:70px">進捗</th>
                    <th style="width:65px;text-align:center">🐛</th>
                    <th style="width:65px;text-align:center">💡</th>
                    <th style="width:90px;text-align:center">権限</th>
                    <th style="width:100px;text-align:center">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($issues as $issue)
                <tr>
                    <td><a href="{{ route('issues.show', $issue) }}" style="color:var(--green-dark);font-weight:700">{{ $issue->issue_key }}</a></td>
                    <td><a href="{{ route('issues.show', $issue) }}">{{ $issue->summary }}</a></td>
                    <td>
                        @php $p = $issue->priority; @endphp
                        <span class="badge {{ $p === 'critical' || $p === 'high' ? 'high' : ($p === 'low' ? 'low' : 'normal') }}">
                            {{ ['critical'=>'最高','high'=>'高','normal'=>'中','low'=>'低'][$p] ?? $p }}
                        </span>
                    </td>
                    <td><span class="status-pill status-{{ $issue->status }}">{{ $statusLabels[$issue->status] ?? $issue->status }}</span></td>
                    <td>{{ $issue->assignee?->name ?? '未設定' }}</td>
                    <td>
                        @php $prog = $issue->overallProgress(); @endphp
                        <div style="display:flex;align-items:center;gap:4px">
                            <div class="progress" style="flex:1"><span style="width:{{ $prog }}%"></span></div>
                            <span style="font-size:11px;white-space:nowrap">{{ $prog }}%</span>
                        </div>
                    </td>
                    <td style="text-align:center">
                        @if($issue->bugs_count > 0)
                            <a href="{{ route('bugs.index', $issue) }}" style="color:var(--red);font-weight:700">{{ $issue->bugs_count }}</a>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        @if($issue->feature_requests_count > 0)
                            <a href="{{ route('features.index', $issue) }}" style="color:var(--blue);font-weight:700">{{ $issue->feature_requests_count }}</a>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        @can('update', $issue)
                            <span class="badge normal">編集可</span>
                        @else
                            <span class="badge">閲覧のみ</span>
                        @endcan
                    </td>
                    <td style="text-align:center">
                        @can('update', $issue)
                            @if($issue->isArchived())
                                <form method="post" action="{{ route('issues.unarchive', $issue) }}">
                                    @csrf
                                    @method('patch')
                                    <button class="btn light small">復元</button>
                                </form>
                            @else
                                <form method="post" action="{{ route('issues.archive', $issue) }}" onsubmit="return confirm('案件「{{ $issue->issue_key }}」をアーカイブしますか？\nアーカイブ済み一覧からいつでも復元できます。')">
                                    @csrf
                                    @method('patch')
                                    <button class="btn light small">アーカイブ</button>
                                </form>
                            @endif
                        @else
                            <span class="muted">-</span>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:12px">{{ $issues->links() }}</div>
    @endif
</section>
@endsection
