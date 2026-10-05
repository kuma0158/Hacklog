<x-guest-layout title="アカウント登録 - Hacklog">
    <form method="post" action="{{ route('register') }}">
        @csrf
        <div class="field"><label for="name">名前</label><input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" autofocus required></div>
        <div class="field"><label for="email">メールアドレス</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required></div>
        <div class="field"><label for="password">パスワード</label><input id="password" name="password" type="password" autocomplete="new-password" required></div>
        <div class="field"><label for="password_confirmation">パスワード（確認）</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
        <button class="btn">登録する</button>

        <div class="links">
            <a href="{{ route('login') }}">すでにアカウントをお持ちの方はログイン</a>
        </div>
    </form>
</x-guest-layout>
