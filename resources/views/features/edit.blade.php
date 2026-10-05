@extends('layouts.app')
@section('content')
<div class="top">
    <div>
        <div class="h1">💡 機能要望を編集</div>
        <div class="sub"><a href="{{ route('features.show', [$issue, $feature]) }}">{{ $feature->title }}</a></div>
    </div>
</div>
<section class="card">
    @can('update', $issue)
        <form method="post" action="{{ route('features.update', [$issue, $feature]) }}" class="form-grid">@csrf @method('put')
            @include('features._form', ['feature' => $feature])
            <div style="grid-column:1/-1">
                <button class="btn">更新</button>
                <a class="btn light" href="{{ route('features.show', [$issue, $feature]) }}" style="margin-left:8px">キャンセル</a>
            </div>
        </form>
    @else
        <div class="alert" role="status">この案件は閲覧のみです。機能要望を編集する権限がありません。</div>
    @endcan
</section>
@endsection
