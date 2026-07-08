<?php
declare(strict_types=1);

require __DIR__ . '/config/config.php';

require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/global_functions.php';
require __DIR__ . '/lib/Database.php';
require __DIR__ . '/lib/Security.php';
require __DIR__ . '/lib/CSRF.php';
require __DIR__ . '/lib/Auth.php';
require __DIR__ . '/lib/Validator.php';
require __DIR__ . '/lib/RateLimiter.php';

require __DIR__ . '/Routing/Router.php';

// Autoload app classes (controllers, etc.)
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($path)) {
        require $path;
    }
});

// Start session
App\lib\Session::start();


