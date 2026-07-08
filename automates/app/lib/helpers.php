<?php
declare(strict_types=1);

namespace App\lib;

function config(): array
{
    static $cfg;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../config/config.php';
    }
    return $cfg;
}

function redirect(string $path): void
{
    if ($path !== '' && !preg_match('#^[a-z][a-z0-9+.-]*://#i', $path)) {
        $path = base_url($path);
    }
    header('Location: ' . $path);
    exit;
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string
{
    $cfg = config();
    $baseUrl = rtrim((string)($cfg['app']['base_url'] ?? ''), '/');
    $path = trim($path);

    if ($path === '' || $path === '/') {
        return $baseUrl === '' ? '/' : $baseUrl . '/';
    }

    if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $path)) {
        return $path;
    }

    $path = '/' . ltrim($path, '/');

    if ($baseUrl !== '' && ($path === $baseUrl || str_starts_with($path, $baseUrl . '/'))) {
        return $path;
    }

    return $baseUrl . $path;
}

function csrf_field(): void
{
    $token = CSRF::token();
    echo '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

