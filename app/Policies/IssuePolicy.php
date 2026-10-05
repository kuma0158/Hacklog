<?php

namespace App\Policies;

use App\Models\Issue;
use App\Models\User;

class IssuePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Issue $issue): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    /**
     * 案件の編集はログインしていれば誰でも行える。管理者と一般ユーザーの差は
     * ユーザー管理（manage-users）と案件の新規作成（create）の可否だけ。
     */
    public function update(User $user, Issue $issue): bool
    {
        return true;
    }

    public function delete(User $user, Issue $issue): bool
    {
        return $this->update($user, $issue);
    }

    public function restore(User $user, Issue $issue): bool
    {
        return $this->update($user, $issue);
    }

    public function forceDelete(User $user, Issue $issue): bool
    {
        return $this->update($user, $issue);
    }
}
