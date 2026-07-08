<?php
declare(strict_types=1);

namespace App\Routing;

use function App\lib\config;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Strip the project base path, and also tolerate the legacy /public URL.
        $cfg = config();
        $basePath = rtrim((string)($cfg['app']['base_url'] ?? ''), '/');
        $pathsToStrip = [];
        if ($basePath !== '') {
            $pathsToStrip[] = $basePath . '/public';
            $pathsToStrip[] = $basePath;
        }
        foreach ($pathsToStrip as $prefix) {
            if ($prefix !== '' && str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
                break;
            }
        }
        if (!$path) {
            $path = '/';
        }

        $handler = $this->routes[$method][$path] ?? null;
        if (!$handler) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        [$class, $func] = $handler;
        $controller = new $class();
        $controller->$func();
    }
}

