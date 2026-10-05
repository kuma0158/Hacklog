@extends('layouts.app')
@section('content')
<div class="top">
    <div><div class="h1">ワークパッケージ編集</div><div class="sub">{{ $package->wbs_code }} {{ $package->name }} ／ {{ $issue->issue_key }}</div></div>
    <div class="actions"><a class="btn light" href="{{ route('work-packages.index', $issue) }}">一覧へ戻る</a></div>
</div>
<section class="card">
    @can('update', $issue)
        <form method="post" action="{{ route('work-packages.update', [$issue, $package]) }}" class="form-grid">@csrf @method('put')
            @include('work-packages.partials.form-fields')
            <div class="actions"><button class="btn">更新</button><a class="btn light" href="{{ route('work-packages.index', $issue) }}">キャンセル</a></div>
        </form>
    @else
        <div class="alert" role="status">この案件は閲覧のみです。ワークパッケージを編集する権限がありません。</div>
    @endcan
</section>
@endsection
