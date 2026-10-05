@extends('layouts.app')
@section('content')
<div class="top">
    <div><div class="h1">ユーザー管理</div><div class="sub">担当者や閲覧者のアカウントを管理します。</div></div>
    <div class="actions"><a class="btn" href="{{ route('users.create') }}">ユーザー追加</a></div>
</div>
<section class="card">
    <table class="table">
        <tr><th>名前</th><th>メール</th><th>権限</th><th>できること</th><th>登録日</th><th style="width:180px">操作</th></tr>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->name }}@if(auth()->id() === $user->id) <span class="badge">あなた</span>@endif</td>
                <td>{{ $user->email }}</td>
                <td><span class="badge {{ $user->role === \App\Models\User::ROLE_ADMIN ? 'normal' : '' }}">{{ $user->roleLabel() }}</span></td>
                <td>
                    @if($user->role === \App\Models\User::ROLE_ADMIN)
                        <span class="muted">全案件の編集 ＋ ユーザー管理・案件作成</span>
                    @else
                        <span class="muted">全案件の編集</span>
                    @endif
                </td>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                <td>
                    <div class="actions">
                        <a class="btn light" href="{{ route('users.edit', $user) }}">編集</a>
                        @if(auth()->id() !== $user->id)
                            <form method="post" action="{{ route('users.destroy', $user) }}">@csrf @method('delete')
                                <button class="btn danger" onclick="return confirm('{{ $user->name }} を削除しますか？')">削除</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">ユーザーが登録されていません。</td></tr>
        @endforelse
    </table>
</section>
@endsection
