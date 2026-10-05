@extends('layouts.app')
@section('content')
<div class="top">
    <div>
        <div class="h1">💡 機能要望を追加</div>
        <div class="sub"><a href="{{ route('features.index', $issue) }}">{{ $issue->issue_key }} 機能要望一覧</a></div>
    </div>
</div>
<section class="card">
    @can('update', $issue)
        <form method="post" action="{{ route('features.store', $issue) }}" class="form-grid">@csrf
            @include('features._form', ['feature' => null])
            <div style="grid-column:1/-1"><button class="btn">登録</button></div>
        </form>
    @else
        <div class="alert" role="status">この案件は閲覧のみです。機能要望を追加する権限がありません。</div>
    @endcan
</section>
@endsection
