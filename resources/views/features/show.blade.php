@extends('layouts.app')
@section('content')
@php
    $priorityLabels = ['critical'=>'最高','high'=>'高','normal'=>'中','low'=>'低'];
@endphp
<div class="top">
    <div>
        <div class="h1">{{ $feature->title }}</div>
        <div class="sub"><a href="{{ route('features.index', $issue) }}">{{ $issue->issue_key }} 機能要望一覧</a></div>
    </div>
    @can('update', $issue)
        <div class="actions">
            <form method="post" action="{{ route('features.vote', [$issue, $feature]) }}">@csrf
                <button class="{{ $hasVoted ? 'btn light' : 'btn secondary' }}" style="min-width:120px">
                    👍 {{ $hasVoted ? '投票取り消し' : '投票する' }} ({{ $feature->vote_count }})
                </button>
            </form>
            <a class="btn light" href="{{ route('features.edit', [$issue, $feature]) }}">編集</a>
            <form method="post" action="{{ route('features.destroy', [$issue, $feature]) }}" onsubmit="return confirm('この機能要望を削除しますか？')">@csrf @method('delete')
                <button class="btn danger">削除</button>
            </form>
        </div>
    @endcan
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
    <div class="alert" role="status">この案件の機能要望は閲覧のみです。投票を含む変更操作は行えません。</div>
@endcannot

<div class="grid cols-2 section-block">
    {{-- 基本情報（ステータス含む・読み取り） --}}
    <section class="card">
        <h2>基本情報</h2>
        <table class="detail-table">
            <tr>
                <th>ステータス</th>
                <td><span class="feat-status feat-status-{{ $feature->status }}">{{ $statusLabels[$feature->status] ?? $feature->status }}</span></td>
            </tr>
            <tr><th>優先度</th><td>{{ $priorityLabels[$feature->priority] ?? $feature->priority }}</td></tr>
            <tr><th>担当者</th><td>{{ $feature->assignee?->name ?? '未設定' }}</td></tr>
            <tr><th>要望者</th><td>{{ $feature->requester?->name ?? '不明' }}</td></tr>
            <tr><th>期限</th><td>{{ $feature->due_date?->format('Y-m-d') ?? '未設定' }}</td></tr>
            <tr><th>登録日</th><td>{{ $feature->created_at->format('Y-m-d H:i') }}</td></tr>
        </table>
    </section>

    <div class="grid">
        {{-- ステータス・担当者クイック更新 --}}
        @can('update', $issue)
            <section class="card">
                <h2>ステータス・担当者を更新</h2>
                <form method="post" action="{{ route('features.update', [$issue, $feature]) }}" class="form-grid">@csrf @method('put')
                {{-- 変更しない項目はhiddenで引き回す --}}
                <input type="hidden" name="title"      value="{{ $feature->title }}">
                <input type="hidden" name="priority"   value="{{ $feature->priority }}">
                <input type="hidden" name="due_date"   value="{{ $feature->due_date?->format('Y-m-d') }}">

                <div class="field full-field">
                    <label>ステータス</label>
                    <select name="status">
                        @foreach($statusLabels as $k => $v)
                            <option value="{{ $k }}" @selected($feature->status === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field full-field">
                    <label>担当者</label>
                    <select name="assignee_id">
                        <option value="">未設定</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected($feature->assignee_id === $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                    <div class="full-field"><button class="btn">更新</button></div>
                </form>
            </section>
        @endcan

        {{-- 投票状況 --}}
        <section class="card summary-card secondary">
            <h2>投票状況</h2>
            <div class="progress-total" style="text-align:center;padding:8px 0">{{ $feature->vote_count }}</div>
            <div class="summary-label" style="text-align:center;margin-bottom:12px">票</div>
            @can('update', $issue)
                <div style="text-align:center">
                    <form method="post" action="{{ route('features.vote', [$issue, $feature]) }}">@csrf
                        <button class="{{ $hasVoted ? 'btn light' : 'btn secondary' }}">
                            👍 {{ $hasVoted ? '投票済み（取り消す）' : '投票する' }}
                        </button>
                    </form>
                </div>
            @endcan
        </section>
    </div>
</div>

@if($feature->description)
<section class="card section-block">
    <h2>詳細・背景</h2>
    <div class="text-prewrap">{{ $feature->description }}</div>
</section>
@endif

@if($feature->user_story)
<section class="card summary-card secondary section-block">
    <h2>ユーザーストーリー</h2>
    <div class="text-prewrap soft-panel">{{ $feature->user_story }}</div>
</section>
@endif


@endsection
