@extends('layouts.app')
@section('content')
<div class="top">
    <div>
        <div class="h1">🐛 バグ編集</div>
        <div class="sub"><a href="{{ route('bugs.show', [$issue, $bug]) }}">{{ $bug->title }}</a></div>
    </div>
</div>
<section class="card">
    @can('update', $issue)
        <form method="post" action="{{ route('bugs.update', [$issue, $bug]) }}" class="form-grid">@csrf @method('put')
            @include('bugs._form', ['bug' => $bug])
            <div style="grid-column:1/-1">
                <button class="btn">更新</button>
                <a class="btn light" href="{{ route('bugs.show', [$issue, $bug]) }}" style="margin-left:8px">キャンセル</a>
            </div>
        </form>
    @else
        <div class="alert" role="status">この案件は閲覧のみです。バグを編集する権限がありません。</div>
    @endcan
</section>
@endsection
