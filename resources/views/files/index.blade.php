@extends('layouts.app')
@section('content')
<div class="top">
    <div><div class="h1">成果物</div><div class="sub">{{ $issue->issue_key }} {{ $issue->summary }}</div></div>
    <div class="actions"><a class="btn light" href="{{ route('issues.show', $issue) }}">案件詳細へ戻る</a></div>
</div>
<nav class="tabs">
    <a href="{{ route('issues.show', $issue) }}">案件詳細</a>
    <a href="{{ route('work-packages.index', $issue) }}">WBS設計</a>
    <a href="{{ route('work-packages.gantt', $issue) }}">ガント</a>
    <a href="{{ route('work-packages.kanban', $issue) }}">カンバン</a>
    <a href="{{ route('wiki.index', $issue) }}">仕様Wiki</a>
    <a href="{{ route('repositories.index', $issue) }}">ソース管理</a>
    <a href="{{ route('files.index', $issue) }}"><strong>成果物</strong></a>
</nav>

@cannot('update', $issue)
    <div class="alert" role="status">この案件の成果物は閲覧のみです。</div>
@endcannot

<div class="grid @can('update', $issue) cols-2 @endcan">
    @can('update', $issue)
        <section class="card">
            <h2>ファイル情報登録</h2>
            <form method="post" action="{{ route('files.store', $issue) }}" class="form-grid">@csrf
                <div class="field"><label>ファイル名</label><input name="name"></div>
                <div class="field"><label>カテゴリ</label><input name="category"></div>
                <div class="field"><label>サイズ(byte)</label><input type="number" name="size" value="0"></div>
                <div class="field"><label>保存先/URL</label><input name="path"></div>
                <div class="field" style="grid-column:1/-1"><label>説明</label><textarea name="description"></textarea></div>
                <div><button class="btn">登録</button></div>
            </form>
        </section>
    @endcan
    <section class="card">
        <h2>ファイル一覧</h2>
        <table class="table"><tr><th>名前</th><th>カテゴリ</th><th>サイズ</th><th>説明</th></tr>
        @forelse($files as $file)
            <tr><td>{{ $file->name }}</td><td>{{ $file->category ?? '-' }}</td><td>{{ number_format($file->size) }}</td><td>{{ $file->description }}</td></tr>
        @empty
            <tr><td colspan="4" class="muted">登録された成果物はありません。</td></tr>
        @endforelse
        </table>
    </section>
</div>
@endsection
