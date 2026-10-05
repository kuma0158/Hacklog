@extends('layouts.app')
@section('content')
@php
    $selectedRole = old('role', \App\Models\User::ROLE_GENERAL);
@endphp
<div class="top">
    <div><div class="h1">ユーザー追加</div><div class="sub">新しいアカウントを登録します。</div></div>
    <div class="actions"><a class="btn light" href="{{ route('users.index') }}">一覧へ戻る</a></div>
</div>
<section class="card">
    <form method="post" action="{{ route('users.store') }}" class="form-grid">@csrf
        <div class="field"><label>名前</label><input name="name" value="{{ old('name') }}" required></div>
        <div class="field"><label>メールアドレス</label><input name="email" type="email" value="{{ old('email') }}" required></div>
        <div class="field">
            <label>権限</label>
            <select name="role" required>
                <option value="{{ \App\Models\User::ROLE_GENERAL }}" @selected($selectedRole === \App\Models\User::ROLE_GENERAL)>一般</option>
                <option value="{{ \App\Models\User::ROLE_ADMIN }}" @selected($selectedRole === \App\Models\User::ROLE_ADMIN)>管理者</option>
            </select>
        </div>
        <div class="field"><label>パスワード (8文字以上)</label><input name="password" type="password" required autocomplete="new-password"></div>
        <div class="field"><label>パスワード(確認)</label><input name="password_confirmation" type="password" required></div>
        <div class="field full-field">
            <div class="soft-panel">
                <p class="muted" style="margin:0">案件の編集は一般ユーザーでもすべての案件で行えます。管理者はこれに加えて、ユーザー管理と案件の新規作成が行えます。</p>
            </div>
        </div>
        <div class="actions" style="grid-column:1/-1"><button class="btn">追加</button><a class="btn light" href="{{ route('users.index') }}">キャンセル</a></div>
    </form>
</section>
@endsection
