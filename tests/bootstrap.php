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
if (!getenv('DB_HOST')) putenv('DB_HOST=127.0.0.1');
if (!getenv('DB_NAME')) putenv('DB_NAME=finzo_test_db');

// Mock SCRIPT_NAME to prevent warnings in routing logic
$_SERVER['SCRIPT_NAME'] = '/index.php';
if (!getenv('DB_USER')) putenv('DB_USER=root');
if (getenv('DB_PASS') === false) putenv('DB_PASS=');
putenv('APP_DEBUG=false');

define('FINZO_ROOT', dirname(__DIR__));
define('FINZO_TESTING', true);

// ── 2. Auto-provision the test database ─────────────────────────────────────
class TestPDO extends PDO {
    protected int $transactionCount = 0;
    public function beginTransaction(): bool {
        if ($this->transactionCount == 0) {
            parent::beginTransaction();
        }
        $this->transactionCount++;
        return true;
    }
    public function commit(): bool {
        $this->transactionCount--;
        if ($this->transactionCount == 0) {
            return parent::commit();
        }
        return true;
    }
    public function rollBack(): bool {
        $this->transactionCount--;
        if ($this->transactionCount == 0) {
            return parent::rollBack();
        }
        return true;
    }
}

try {
    $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $provisionPdo = new TestPDO("mysql:host={$dbHost};port=3306;charset=utf8mb4", $dbUser, $dbPass, [
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

        // Use Migrator to deploy the schema correctly
        require_once FINZO_ROOT . '/database/Migrator.php';
        $migrator = new \Database\Migrator(
            $provisionPdo,
            FINZO_ROOT . '/database/migrations',
            FINZO_ROOT . '/database/seeds'
        );
        $migrator->reset();
        $migrator->migrate();

        // Mark schema as complete
        $provisionPdo->exec("CREATE TABLE IF NOT EXISTS _test_schema_version (version INT NOT NULL DEFAULT 1)");
        echo "[Test Bootstrap] Test DB schema fully imported.\n";
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
require_once FINZO_ROOT . '/vendor/autoload.php';

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


