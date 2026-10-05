@extends('layouts.app')
@section('content')
@php
    $severityLabels = ['critical'=>'🔴 致命的','high'=>'🟠 高','medium'=>'🟡 中','low'=>'🟢 低'];
    $priorityLabels = ['critical'=>'最高','high'=>'高','normal'=>'中','low'=>'低'];
@endphp
<div class="top">
    <div>
        <div class="h1">{{ $bug->title }}</div>
        <div class="sub"><a href="{{ route('bugs.index', $issue) }}">{{ $issue->issue_key }} バグ一覧</a></div>
    </div>
    @can('update', $issue)
        <div class="actions">
            <a class="btn light" href="{{ route('bugs.edit', [$issue, $bug]) }}">編集</a>
            <form method="post" action="{{ route('bugs.destroy', [$issue, $bug]) }}" onsubmit="return confirm('このバグを削除しますか？')">@csrf @method('delete')
                <button class="btn danger">削除</button>
            </form>
        </div>
    @endcan
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

<div class="grid @can('update', $issue) cols-2 @endcan section-block">
    {{-- 基本情報（ステータス含む・読み取り） --}}
    <section class="card">
        <h2>基本情報</h2>
        <table class="detail-table">
            <tr>
                <th>ステータス</th>
                <td><span class="bug-status bug-status-{{ $bug->status }}">{{ $statusLabels[$bug->status] ?? $bug->status }}</span></td>
            </tr>
            <tr><th>深刻度</th><td>{{ $severityLabels[$bug->severity] ?? $bug->severity }}</td></tr>
            <tr><th>優先度</th><td>{{ $priorityLabels[$bug->priority] ?? $bug->priority }}</td></tr>
            <tr><th>担当者</th><td>{{ $bug->assignee?->name ?? '未設定' }}</td></tr>
            <tr><th>報告者</th><td>{{ $bug->reporter?->name ?? '不明' }}</td></tr>
            <tr><th>期限</th><td>{{ $bug->due_date?->format('Y-m-d') ?? '未設定' }}</td></tr>
            <tr><th>環境</th><td>{{ $bug->environment_info ?: '未記入' }}</td></tr>
            <tr><th>登録日</th><td>{{ $bug->created_at->format('Y-m-d H:i') }}</td></tr>
        </table>
    </section>

    {{-- ステータス・担当者クイック更新 --}}
    @can('update', $issue)
        <section class="card">
            <h2>ステータス・担当者を更新</h2>
            <form method="post" action="{{ route('bugs.update', [$issue, $bug]) }}" class="form-grid">@csrf @method('put')
            {{-- 変更しない項目はhiddenで引き回す --}}
            <input type="hidden" name="title"            value="{{ $bug->title }}">
            <input type="hidden" name="severity"         value="{{ $bug->severity }}">
            <input type="hidden" name="priority"         value="{{ $bug->priority }}">
            <input type="hidden" name="due_date"         value="{{ $bug->due_date?->format('Y-m-d') }}">
            <input type="hidden" name="environment_info" value="{{ $bug->environment_info }}">

            <div class="field" style="grid-column:1/-1">
                <label>ステータス</label>
                <select name="status">
                    @foreach($statusLabels as $k => $v)
                        <option value="{{ $k }}" @selected($bug->status === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field" style="grid-column:1/-1">
                <label>担当者</label>
                <select name="assignee_id">
                    <option value="">未設定</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected($bug->assignee_id === $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

                <div style="grid-column:1/-1"><button class="btn">更新</button></div>
            </form>
        </section>
    @endcan
</div>

@if($bug->description)
<section class="card section-block">
    <h2>詳細・概要</h2>
    <div class="text-prewrap">{{ $bug->description }}</div>
</section>
@endif

@if($bug->steps_to_reproduce)
<section class="card summary-card section-block">
    <h2>再現手順</h2>
    <div class="text-prewrap soft-panel">{{ $bug->steps_to_reproduce }}</div>
</section>
@endif

@if($bug->expected_behavior || $bug->actual_behavior)
<div class="grid cols-2 section-block">
    @if($bug->expected_behavior)
    <section class="card summary-card secondary">
        <h2>期待される動作</h2>
        <div class="text-prewrap">{{ $bug->expected_behavior }}</div>
    </section>
    @endif
    @if($bug->actual_behavior)
    <section class="card summary-card">
        <h2>実際の動作</h2>
        <div class="text-prewrap">{{ $bug->actual_behavior }}</div>
    </section>
    @endif
</div>
@endif


@endsection
