<x-guest-layout title="ログイン - Hacklog">
    <form method="post" action="{{ route('login') }}">
        @csrf
        <div class="field"><label for="email">メールアドレス</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" autofocus required></div>
        <div class="field"><label for="password">パスワード</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
        <label class="remember"><input name="remember" type="checkbox" value="1">ログイン状態を保持する</label>
        <button class="btn">ログイン</button>

        <div class="links">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">パスワードをお忘れですか？</a>
            @endif
            <!--@if (Route::has('register'))
                <a href="{{ route('register') }}">アカウント登録</a>
            @endif-->
        </div>
    </form>
</x-guest-layout>
