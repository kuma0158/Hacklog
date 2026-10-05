<x-guest-layout title="パスワードの確認 - Hacklog">
    <p class="lead">セキュリティ保護のため、続行するにはパスワードを確認してください。</p>

    <form method="post" action="{{ route('password.confirm') }}">
        @csrf
        <div class="field"><label for="password">パスワード</label><input id="password" name="password" type="password" autocomplete="current-password" autofocus required></div>
        <button class="btn">確認する</button>
    </form>
</x-guest-layout>
