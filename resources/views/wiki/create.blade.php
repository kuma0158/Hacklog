@extends('layouts.app')
@section('content')
<div class="top">
    <div>
        <div class="h1">Wiki新規作成</div>
        <div class="sub">{{ $issue->issue_key }} {{ $issue->summary }} の新しいページをMarkdownで作成します。</div>
    </div>
    <div class="actions">
        <a class="btn light" href="{{ route('wiki.index', $issue) }}">一覧へ戻る</a>
    </div>
</div>

<section class="card">
    @can('update', $issue)
        <form method="post" action="{{ route('wiki.store', $issue) }}" class="form-grid">@csrf
            <div class="field"><label>番号(例: 1, 1.1, 1.2.1)</label><input name="number" value="{{ old('number') }}" placeholder="未入力の場合は一覧の末尾に表示"></div>
            <div class="field" style="grid-column:1/-1"><label>タイトル</label><input name="title" value="{{ old('title') }}"></div>
            <div class="field" style="grid-column:1/-1"><label>Markdown本文</label><textarea name="body">{{ old('body') }}</textarea></div>
            <div class="actions" style="grid-column:1/-1">
                <button class="btn">作成</button>
            </div>
        </form>
    @else
        <div class="alert" role="status">この案件は閲覧のみです。Wikiページを作成する権限がありません。</div>
    @endcan
</section>
@endsection
