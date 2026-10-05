@extends('layouts.app')
@section('content')
<div class="top">
    <div><div class="h1">ソース管理</div><div class="sub">{{ $issue->issue_key }} {{ $issue->summary }} の Git / Subversion リポジトリ</div></div>
    <div class="actions"><a class="btn light" href="{{ route('issues.show', $issue) }}">案件詳細へ戻る</a></div>
</div>
<nav class="tabs">
    <a href="{{ route('issues.show', $issue) }}">案件詳細</a>
    <a href="{{ route('work-packages.index', $issue) }}">WBS設計</a>
    <a href="{{ route('work-packages.gantt', $issue) }}">ガント</a>
    <a href="{{ route('work-packages.kanban', $issue) }}">カンバン</a>
    <a href="{{ route('wiki.index', $issue) }}">仕様Wiki</a>
    <a href="{{ route('repositories.index', $issue) }}"><strong>ソース管理</strong></a>
    <a href="{{ route('files.index', $issue) }}">成果物</a>
</nav>

@cannot('update', $issue)
    <div class="alert" role="status">この案件のリポジトリ情報は閲覧のみです。</div>
@endcannot

<div class="grid @can('update', $issue) cols-2 @endcan">
    @can('update', $issue)
        <section class="card">
            <h2>リポジトリ登録</h2>
            <form method="post" action="{{ route('repositories.store', $issue) }}" class="form-grid">@csrf
                <div class="field"><label>名称</label><input name="name"></div>
                <div class="field"><label>種別</label><select name="type"><option value="git">Git</option><option value="svn">Subversion</option></select></div>
                <div class="field" style="grid-column:1/-1"><label>URL</label><input name="url"></div>
                <div class="field" style="grid-column:1/-1"><label>説明</label><textarea name="description"></textarea></div>
                <div><button class="btn">登録</button></div>
            </form>
        </section>
    @endcan
    <section class="card">
        <h2>リポジトリ一覧</h2>
        @forelse($repositories as $repository)
            <article style="border-bottom:1px solid var(--line);padding:10px 0">
                <strong>{{ $repository->name }}</strong> <span class="badge">{{ strtoupper($repository->type) }}</span>
                <p>{{ $repository->description }}</p>
                <code>{{ $repository->url }}</code>
            </article>
        @empty
            <p class="muted">登録されたリポジトリはありません。</p>
        @endforelse
    </section>
</div>
@endsection
