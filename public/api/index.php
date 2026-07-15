<?php

// Boot up the application (Autoloader, Env, Config, Error Handlers)
require_once __DIR__ . '/../../bootstrap/app.php';

// Load routes
$router = require_once FINZO_ROOT . '/routes/api.php';

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$router->dispatch($method, $uri);

