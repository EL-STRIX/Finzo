<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';

// Autoloader fallback if composer is not fully configured for these namespaces
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load routes
$router = require_once __DIR__ . '/../routes/api.php';

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$router->dispatch($method, $uri);
