@extends('layouts.app')
@section('content')
<div class="top">
    <div>
        <div class="h1">Wiki編集</div>
        <div class="sub">{{ $page->title }} ／ {{ $issue->issue_key }}</div>
    </div>
    <div class="actions">
        <a class="btn light" href="{{ route('wiki.show', [$issue, $page]) }}">表示へ戻る</a>
    </div>
</div>

<section class="card">
    @can('update', $issue)
        <form method="post" action="{{ route('wiki.update', [$issue, $page]) }}" class="form-grid">@csrf @method('put')
            <div class="field"><label>番号(例: 1, 1.1, 1.2.1)</label><input name="number" value="{{ old('number', $page->number) }}" placeholder="未入力の場合は一覧の末尾に表示"></div>
            <div class="field" style="grid-column:1/-1"><label>タイトル</label><input name="title" value="{{ old('title', $page->title) }}"></div>
            <div class="field" style="grid-column:1/-1"><label>Markdown本文</label><textarea name="body" style="min-height:60vh;font-family:Consolas,Monaco,monospace;resize:vertical">{{ old('body', $page->body) }}</textarea></div>
            <div class="actions" style="grid-column:1/-1">
                <button class="btn">更新</button>
            </div>
        </form>
    @else
        <div class="alert" role="status">この案件は閲覧のみです。Wikiページを編集する権限がありません。</div>
    @endcan
</section>
@endsection
