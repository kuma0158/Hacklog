<x-guest-layout title="メールアドレスの確認 - Hacklog">
    <p class="lead">ご登録ありがとうございます。ご登録のメールアドレスに送信された確認リンクをクリックして、メールアドレスの確認を完了してください。メールが届いていない場合は再送できます。</p>

    @if (session('status') === 'verification-link-sent')
        <div class="alert">確認メールを再送しました。ご登録のメールアドレスをご確認ください。</div>
    @endif

    <form method="post" action="{{ route('verification.send') }}" style="margin-bottom:10px">
        @csrf
        <button class="btn">確認メールを再送する</button>
    </form>

    <form method="post" action="{{ route('logout') }}">
        @csrf
        <button class="btn light">ログアウト</button>
    </form>
</x-guest-layout>
