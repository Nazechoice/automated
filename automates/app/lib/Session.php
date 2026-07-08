<?php
declare(strict_types=1);

namespace App\lib;

final class Session
{
    public static function start(): void
    {
        $config = require __DIR__ . '/../config/config.php';
        $name = $config['app']['session_name'] ?? 'AUTOMATES_SID';

        if (session_status() === PHP_SESSION_NONE) {
            session_name($name);
            session_start([
                'cookie_path' => '/',
                'cookie_httponly' => true,
                'cookie_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'cookie_samesite' => 'Lax',
                'use_strict_mode' => true,
            ]);
        }
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
    }
}

