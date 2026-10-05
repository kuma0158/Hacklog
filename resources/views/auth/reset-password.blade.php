<x-guest-layout title="新しいパスワードの設定 - Hacklog">
    <p class="lead">新しいパスワードを設定してください。</p>

    <form method="post" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="field"><label for="email">メールアドレス</label><input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" autocomplete="username" autofocus required></div>
        <div class="field"><label for="password">新しいパスワード</label><input id="password" name="password" type="password" autocomplete="new-password" required></div>
        <div class="field"><label for="password_confirmation">新しいパスワード（確認）</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
        <button class="btn">パスワードを再設定</button>
    </form>
</x-guest-layout>
