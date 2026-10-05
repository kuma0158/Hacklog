<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::orderBy('role')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:80'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', Password::defaults(), 'confirmed'],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        User::create($data);

        return redirect()->route('users.index')->with('status', 'ユーザーを追加しました。');
    }

    public function edit(User $user): View
    {
        return view('users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:80'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', Password::defaults(), 'confirmed'],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        $passwordChanged = ! empty($data['password']);
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $updatedUser = DB::transaction(function () use ($user, $data, $passwordChanged): User {
            $admins = User::where('role', User::ROLE_ADMIN)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($lockedUser->isAdmin()
                && $data['role'] !== User::ROLE_ADMIN
                && $admins->count() === 1) {
                throw ValidationException::withMessages([
                    'role' => '最後の管理者を一般ユーザーに変更することはできません。',
                ]);
            }

            $lockedUser->fill($data);

            if ($passwordChanged) {
                $lockedUser->setRememberToken(Str::random(60));
            }

            $lockedUser->save();

            return $lockedUser;
        }, 5);

        if (Auth::id() === $updatedUser->id && ! $updatedUser->isAdmin()) {
            return redirect()->route('dashboard')->with('status', 'ユーザー情報を更新しました。');
        }

        return redirect()->route('users.index')->with('status', 'ユーザー情報を更新しました。');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return back()->withErrors(['user' => 'ログイン中のユーザーは削除できません。']);
        }

        DB::transaction(function () use ($user): void {
            $admins = User::where('role', User::ROLE_ADMIN)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($lockedUser->isAdmin() && $admins->count() === 1) {
                throw ValidationException::withMessages([
                    'user' => '最後の管理者は削除できません。',
                ]);
            }

            $lockedUser->delete();
        }, 5);

        return redirect()->route('users.index')->with('status', 'ユーザーを削除しました。');
    }
}
