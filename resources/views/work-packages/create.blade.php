@extends('layouts.app')
@section('content')
<div class="top">
    <div><div class="h1">ワークパッケージ追加</div><div class="sub">{{ $issue->issue_key }} {{ $issue->summary }} のWBSに追加します。</div></div>
    <div class="actions"><a class="btn light" href="{{ route('work-packages.index', $issue) }}">一覧へ戻る</a></div>
</div>
<section class="card">
    @can('update', $issue)
        <form method="post" action="{{ route('work-packages.store', $issue) }}" class="form-grid">@csrf
            @include('work-packages.partials.form-fields')
            <div class="actions"><button class="btn">追加</button><a class="btn light" href="{{ route('work-packages.index', $issue) }}">キャンセル</a></div>
        </form>
    @else
        <div class="alert" role="status">この案件は閲覧のみです。ワークパッケージを追加する権限がありません。</div>
    @endcan
</section>
@endsection
