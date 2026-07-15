<?php
/**
 * FINZO Enterprise Application Bootstrap
 * Handles PSR-4 Autoloading and core initialization.
 */

if (!defined('FINZO_ROOT')) {
    define('FINZO_ROOT', dirname(__DIR__));
}

// Custom PSR-4 Autoloader
spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'App\\';
    
    // Base directory for the namespace prefix
    $base_dir = FINZO_ROOT . '/app/';
    
    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace namespace separators with directory separators, append with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

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

// Global Exception Handler for API standard
set_exception_handler(function (\Throwable $e) {
    $debug = getenv('APP_DEBUG') === 'true';
    $statusCode = 500;
    
    // Check if it's our custom ApiException to inherit status codes
    if (method_exists($e, 'getStatusCode')) {
        $statusCode = $e->getStatusCode();
    }
    
    if (!headers_sent()) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
    }
    
    $response = [
        'success' => false,
        'message' => $debug ? $e->getMessage() : 'A server error occurred. Please try again later.'
    ];
    
    if (method_exists($e, 'getPayload') && !empty($e->getPayload())) {
        $response = array_merge($response, $e->getPayload());
    }
    
    if ($debug) {
        $response['file'] = $e->getFile();
        $response['line'] = $e->getLine();
    }
    
    error_log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo json_encode($response);
    exit;
});

// Global Error Handler for API standard
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

// Load legacy configuration and functions
require_once FINZO_ROOT . '/config/config.php';
require_once FINZO_ROOT . '/config/db.php';
require_once FINZO_ROOT . '/config/functions.php';

// If middleware files exist, load them or they will be called manually
