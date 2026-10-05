@extends('layouts.app')
@section('content')
<div class="top">
    <div>
        <div class="h1">案件追加</div>
        <div class="sub">新しい案件（タスク・バグ・機能要望）を登録します。</div>
    </div>
    <div class="actions"><a class="btn light" href="{{ route('issues.index') }}">案件一覧へ戻る</a></div>
</div>
<section class="card">
    <form method="post" action="{{ route('issues.store') }}" class="form-grid">@csrf
        @include('issues.form', ['issue' => null])
        <div><button class="btn">登録</button></div>
    </form>
</section>
@endsection
