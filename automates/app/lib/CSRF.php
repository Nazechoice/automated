<?php
declare(strict_types=1);

namespace App\lib;

final class CSRF
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['csrf_token'];
    }

    public static function verify(?string $token): void
    {
        $t = $token ?? '';
        $valid = !empty($_SESSION['csrf_token']) && hash_equals((string)$_SESSION['csrf_token'], $t);
        if (!$valid) {
            http_response_code(403);
            exit('CSRF validation failed');
        }
    }
}

