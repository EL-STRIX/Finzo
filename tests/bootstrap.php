<?php
/**
 * Test Bootstrap File — Finzo Testing Suite
 *
 * This file sets up the isolated testing environment:
 * 1. Forces env vars for the test database (finzo_test_db).
 * 2. Auto-provisions the test database + schema.
 * 3. Loads the PSR-4 autoloader and config functions.
 * 4. Registers the Tests\ namespace autoloader.
 */

// ── 1. Set all env vars FIRST (before anything reads them) ──────────────────
putenv('APP_ENV=testing');
putenv('DB_HOST=127.0.0.1');
putenv('DB_NAME=finzo_test_db');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('APP_DEBUG=false');

define('FINZO_ROOT', dirname(__DIR__));
define('FINZO_TESTING', true);

// ── 2. Auto-provision the test database ─────────────────────────────────────
try {
    $provisionPdo = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $provisionPdo->exec("CREATE DATABASE IF NOT EXISTS finzo_test_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $provisionPdo->exec("USE finzo_test_db");

    // Always re-import if schema_version marker table is missing or columns mismatch
    $versionCheck = $provisionPdo->query("SHOW TABLES LIKE '_test_schema_version'");
    $needsImport = ($versionCheck->rowCount() === 0);

    if ($needsImport) {
        // Drop all tables in the test DB to start fresh
        $provisionPdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $tables = $provisionPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $provisionPdo->exec("DROP TABLE IF EXISTS `$table`");
        }
        $provisionPdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        // Import base schema (strip USE finzo_db)
        $schemaSql = file_get_contents(FINZO_ROOT . '/database/schema/finzo_database.sql');
        $schemaSql = preg_replace('/\bUSE\s+`?finzo_db`?\s*;/i', '', $schemaSql);
        $provisionPdo->exec($schemaSql);

        // Apply all schema update patches
        foreach (['update_admin_schema.sql', 'update_staff_schema.sql', 'update_admin_lock_schema.sql'] as $patch) {
            $patchPath = FINZO_ROOT . '/database/schema/' . $patch;
            if (file_exists($patchPath)) {
                $patchSql = file_get_contents($patchPath);
                // Strip USE finzo_db if present in patches
                $patchSql = preg_replace('/\bUSE\s+`?finzo_db`?\s*;/i', '', $patchSql);
                $provisionPdo->exec($patchSql);
            }
        }

        // Mark schema as complete
        $provisionPdo->exec("CREATE TABLE _test_schema_version (version INT NOT NULL DEFAULT 1)");
        echo "[Test Bootstrap] Test DB schema fully imported (base + all patches).\n";
    }

    // Set the global $pdo so Database::getInstance() finds it
    global $pdo;
    $provisionPdo->exec("USE finzo_test_db");
    $pdo = $provisionPdo;

} catch (PDOException $e) {
    echo "[Test Bootstrap] FATAL: Could not provision test database: " . $e->getMessage() . "\n";
    exit(1);
}

// ── 3. Load PSR-4 autoloader and core config/functions ──────────────────────
// We load each piece manually to avoid config/db.php creating a new connection
// that would overwrite our global $pdo.
spl_autoload_register(function ($class) {
    $prefix  = 'App\\';
    $baseDir = FINZO_ROOT . '/app/';
    $len     = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative = substr($class, $len);
    $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Load config constants (but NOT db.php — we already have $pdo)
require_once FINZO_ROOT . '/config/config.php';
require_once FINZO_ROOT . '/config/functions.php';

// ── 4. Register Tests\ namespace autoloader ──────────────────────────────────
spl_autoload_register(function ($class) {
    $prefix  = 'Tests\\';
    $baseDir = __DIR__ . '/';
    $len     = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative = substr($class, $len);
    $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});


