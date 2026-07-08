<?php
declare(strict_types=1);

namespace App\lib;

use App\models\User;

final class Auth
{
    public static function user(): ?User
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        $user = User::findById((int)$_SESSION['user_id']);
        if (!$user || $user->status !== 'active') {
            self::logout();
            return null;
        }

        if (($_SESSION['role'] ?? null) !== $user->role) {
            $_SESSION['role'] = $user->role;
        }

        return $user;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function role(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::user()) {
            redirect('/login');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if ((self::role() ?? '') !== 'admin') {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}

