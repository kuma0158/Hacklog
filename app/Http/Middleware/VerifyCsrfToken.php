<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // ログアウトは冪等で CSRF リスクが低い。セッション/トークン期限切れでも
        // 419 Page Expired にせず確実にログアウトできるよう検証対象から除外する。
        'logout',
    ];
}
