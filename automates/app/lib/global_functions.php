<?php
declare(strict_types=1);

if (!function_exists('config')) {
    function config(): array
    {
        return \App\lib\config();
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        \App\lib\redirect($path);
    }
}

if (!function_exists('e')) {
    function e(string $s): string
    {
        return \App\lib\e($s);
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        return \App\lib\base_url($path);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): void
    {
        \App\lib\csrf_field();
    }
}
