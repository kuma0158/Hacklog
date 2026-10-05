@extends('layouts.app')
@section('content')
<div class="top">
    <div>
        <div class="h1">🐛 バグ登録</div>
        <div class="sub"><a href="{{ route('bugs.index', $issue) }}">{{ $issue->issue_key }} バグ一覧</a></div>
    </div>
</div>
<section class="card">
    @can('update', $issue)
        <form method="post" action="{{ route('bugs.store', $issue) }}" class="form-grid">@csrf
            @include('bugs._form', ['bug' => null])
            <div style="grid-column:1/-1"><button class="btn">登録</button></div>
        </form>
    @else
        <div class="alert" role="status">この案件は閲覧のみです。バグを登録する権限がありません。</div>
    @endcan
</section>
@endsection
