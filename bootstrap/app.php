<?php
/**
 * FINZO Enterprise Application Bootstrap
 * Handles PSR-4 Autoloading and core initialization.
 */

define('FINZO_ROOT', dirname(__DIR__));

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

// Load legacy configuration and functions
require_once FINZO_ROOT . '/config/config.php';
require_once FINZO_ROOT . '/config/db.php';
require_once FINZO_ROOT . '/config/functions.php';

// If middleware files exist, load them or they will be called manually
