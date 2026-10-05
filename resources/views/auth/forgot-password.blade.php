<x-guest-layout title="パスワードリセット - Hacklog">
    <p class="lead">パスワードをお忘れの場合は、登録済みのメールアドレスを入力してください。パスワードリセット用のリンクをお送りします。</p>

    <form method="post" action="{{ route('password.email') }}">
        @csrf
        <div class="field"><label for="email">メールアドレス</label><input id="email" name="email" type="email" value="{{ old('email') }}" autofocus required></div>
        <button class="btn">リセットリンクを送信</button>

        <div class="links">
            <a href="{{ route('login') }}">ログイン画面に戻る</a>
        </div>
    </form>
</x-guest-layout>
