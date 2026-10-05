@extends('layouts.app', ['title' => 'プロフィール - Hacklog'])

@section('content')
    <div class="top">
        <div>
            <div class="h1">プロフィール</div>
            <div class="sub">アカウント情報とパスワードの管理</div>
        </div>
    </div>

    <div class="grid" style="max-width:720px">
        <div class="card">
            <h2>プロフィール情報</h2>
            <form method="post" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')
                <div class="form-grid">
                    <div class="field"><label for="name">名前</label><input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required></div>
                    <div class="field"><label for="email">メールアドレス</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="username" required></div>
                    <div class="full-field actions"><button class="btn">保存する</button></div>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>権限</h2>
            <p style="margin-top:0"><span class="badge {{ $user->role === \App\Models\User::ROLE_ADMIN ? 'normal' : '' }}">{{ $user->roleLabel() }}</span></p>
            @if($user->role === \App\Models\User::ROLE_ADMIN)
                <p class="muted" style="margin-bottom:0">案件の編集に加えて、ユーザー管理と案件の新規作成が行えます。</p>
            @else
                <p class="muted" style="margin-bottom:0">すべての案件を編集できます。ユーザー管理と案件の新規作成は管理者のみが行えます。</p>
            @endif
        </div>

        <div class="card">
            <h2>パスワード変更</h2>
            @if ($errors->updatePassword->any())
                <div class="errors">{{ $errors->updatePassword->first() }}</div>
            @endif
            <form method="post" action="{{ route('password.update') }}">
                @csrf
                @method('put')
                <div class="form-grid">
                    <div class="full-field field"><label for="current_password">現在のパスワード</label><input id="current_password" name="current_password" type="password" autocomplete="current-password"></div>
                    <div class="field"><label for="password">新しいパスワード</label><input id="password" name="password" type="password" autocomplete="new-password"></div>
                    <div class="field"><label for="password_confirmation">新しいパスワード（確認）</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"></div>
                    <div class="full-field actions"><button class="btn">パスワードを変更する</button></div>
                </div>
            </form>
        </div>

        <div class="card">
            <h2 class="text-danger">アカウント削除</h2>
            <p class="muted" style="margin-top:0">アカウントを削除すると、すべてのデータが完全に削除されます。この操作は取り消せません。</p>
            @if ($errors->userDeletion->any())
                <div class="errors">{{ $errors->userDeletion->first() }}</div>
            @endif
            <form method="post" action="{{ route('profile.destroy') }}" onsubmit="return confirm('本当にアカウントを削除しますか？この操作は取り消せません。')">
                @csrf
                @method('delete')
                <div class="form-grid">
                    <div class="full-field field"><label for="delete_password">パスワードを入力して確認</label><input id="delete_password" name="password" type="password" autocomplete="current-password"></div>
                    <div class="full-field actions"><button class="btn danger">アカウントを削除する</button></div>
                </div>
            </form>
        </div>
    </div>
@endsection
