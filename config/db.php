<?php
/**
 * Finzo Banking System — Database Connection
 * PDO with MySQL driver, utf8mb4 charset, exception error mode.
 * Version 1.0 — April 2026
 */

require_once __DIR__ . '/config.php';

$db_connected = false;
$pdo = null;

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Add MySQL specific options if driver is available (PHP 8.5 uses Pdo\Mysql::ATTR_INIT_COMMAND)
    $init_command_key = defined('Pdo\Mysql::ATTR_INIT_COMMAND')
        ? \Pdo\Mysql::ATTR_INIT_COMMAND
        : (defined('PDO::MYSQL_ATTR_INIT_COMMAND') ? PDO::MYSQL_ATTR_INIT_COMMAND : null);
    if ($init_command_key !== null) {
        $options[$init_command_key] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
    }

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $db_connected = true;

} catch (PDOException $e) {
    $db_connected = false;
    
    // In debug mode, show detailed error
    if (defined('APP_DEBUG') && APP_DEBUG) {
        $error_message = 'Database connection failed: ' . $e->getMessage();
    } else {
        $error_message = 'Database connection failed. Please try again later.';
    }

    // If this is an API request, return JSON error
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $error_message
        ]);
        exit;
    } else {
        // CLI usage — throw exception
        throw new RuntimeException($error_message);
    }
}
