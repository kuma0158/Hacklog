@extends('layouts.app')
@section('content')
<div class="top">
    <div>
        <div class="h1">仕様Wiki</div>
        <div class="sub">{{ $issue->issue_key }} {{ $issue->summary }}</div>
    </div>
    <div class="actions">
        @can('update', $issue)
            <a class="btn" href="{{ route('wiki.create', $issue) }}">新規ページ</a>
        @endcan
        <a class="btn light" href="{{ route('issues.show', $issue) }}">案件詳細へ戻る</a>
    </div>
</div>
<nav class="tabs">
    <a href="{{ route('issues.show', $issue) }}">案件詳細</a>
    <a href="{{ route('work-packages.index', $issue) }}">WBS設計</a>
    <a href="{{ route('work-packages.gantt', $issue) }}">ガント</a>
    <a href="{{ route('work-packages.kanban', $issue) }}">カンバン</a>
    <a href="{{ route('wiki.index', $issue) }}"><strong>仕様Wiki</strong></a>
    <a href="{{ route('repositories.index', $issue) }}">ソース管理</a>
    <a href="{{ route('files.index', $issue) }}">成果物</a>
</nav>

@cannot('update', $issue)
    <div class="alert" role="status">この案件のWikiは閲覧のみです。エクスポートは引き続き利用できます。</div>
@endcannot

<section class="card">
    <h2>Wiki一覧</h2>
    <table class="table">
        <tr><th style="width:90px">番号</th><th>タイトル</th><th>更新者</th><th>更新日時</th><th style="width:160px">操作</th></tr>
        @forelse($pages as $page)
            <tr>
                <td>{{ $page->number ?? '-' }}</td>
                <td><a href="{{ route('wiki.show', [$issue, $page]) }}">{{ $page->title }}</a></td>
                <td>{{ $page->author?->name ?? '-' }}</td>
                <td>{{ $page->updated_at->format('Y-m-d H:i') }}</td>
                <td>
                    <div class="actions">
                        <a class="btn light small" href="{{ route('wiki.export', [$issue, $page]) }}">エクスポート</a>
                        @can('update', $issue)
                            <a class="btn light small" href="{{ route('wiki.edit', [$issue, $page]) }}">編集</a>
                            <form method="post" action="{{ route('wiki.destroy', [$issue, $page]) }}">@csrf @method('delete')
                                <button type="submit" class="btn danger small" onclick="return confirm('「{{ $page->title }}」を削除しますか？')">削除</button>
                            </form>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">Wikiページはまだありません。</td></tr>
        @endforelse
    </table>
</section>
@endsection
