<?php
/**
 * Test Bootstrap File
 */

// Force testing environment variables
putenv('APP_ENV=testing');
putenv('DB_NAME=finzo_test_db');
putenv('APP_DEBUG=true');
putenv('DB_USER=root');
putenv('DB_PASS='); // Empty string for default XAMPP/CI
putenv('DB_HOST=127.0.0.1');

define('FINZO_TESTING', true);

// Pre-provision the test database before app.php tries to connect
try {
    $pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Create DB if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS finzo_test_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE finzo_test_db");
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() === 0) {
        // Import schema
        $sql = file_get_contents(dirname(__DIR__) . '/database/schema/finzo_database.sql');
        // Strip USE finzo_db to use our current finzo_test_db
        $sql = preg_replace('/USE\s+finzo_db\s*;/i', '', $sql);
        $pdo->exec($sql);
        
        // Apply updates
        $updates = [
            'update_admin_schema.sql',
            'update_staff_schema.sql',
            'update_admin_lock_schema.sql'
        ];
        foreach ($updates as $update) {
            $upSql = file_get_contents(dirname(__DIR__) . '/database/schema/' . $update);
            $pdo->exec($upSql);
        }
    }
} catch (PDOException $e) {
    echo "Warning: Could not auto-provision test database: " . $e->getMessage() . "\n";
    // We don't exit here, we let the normal bootstrap fail if it's fatal
}

require dirname(__DIR__) . '/bootstrap/app.php';

// Ensure they stay overridden
putenv('APP_ENV=testing');
putenv('DB_NAME=finzo_test_db');

// Add autoloader for the Tests namespace
spl_autoload_register(function ($class) {
    $prefix = 'Tests\\';
    $base_dir = __DIR__ . '/';
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

