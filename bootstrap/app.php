<?php

/**
 * FINZO Enterprise Application Bootstrap
 * Handles PSR-4 Autoloading and core initialization.
 */

if (!defined('FINZO_ROOT')) {
    define('FINZO_ROOT', dirname(__DIR__));
}

// Include Composer Autoloader
require_once FINZO_ROOT . '/vendor/autoload.php';

// Load .env file if exists
$envFile = FINZO_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip full-line comments and empty lines
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Remove inline comments that are not inside quotes
            if (strpos($value, '#') !== false) {
                // If it starts with quotes, we'll assume it's quoted entirely (simple parser)
                if (!preg_match('/^([\'"]).*\1$/', $value)) {
                    $valueParts = explode('#', $value, 2);
                    $value = trim($valueParts[0]);
                }
            }

            // Remove wrapping quotes if present
            if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }

            // Populate environment variables
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Populate $_POST for PUT/PATCH requests
if (in_array($_SERVER['REQUEST_METHOD'] ?? '', ['PUT', 'PATCH'])) {
    parse_str(file_get_contents('php://input'), $_POST);
}

// Register global error/exception handlers only if we are not running tests
if (getenv('APP_ENV') !== 'testing') {
    // Global Exception Handler — delegates to the centralized ExceptionHandler
    // for a single, consistent JSON response format across the entire application.
    set_exception_handler(function (\Throwable $e) {
        \App\Shared\Core\Exceptions\ExceptionHandler::handle($e);
    });

    // Global Error Handler for API standard
    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    });
}

// Load legacy configuration and functions
require_once FINZO_ROOT . '/config/config.php';
require_once FINZO_ROOT . '/config/db.php';
require_once FINZO_ROOT . '/config/functions.php';

// Load Event Listeners
require_once FINZO_ROOT . '/config/events.php';

// Initialize IoC Container and register bindings
$container = \App\Shared\Core\Container::getInstance();
\App\Shared\Core\AppServiceProvider::register($container);

// If middleware files exist, load them or they will be called manually
