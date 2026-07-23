<?php
/**
 * Test Bootstrap File — Finzo Testing Suite
 *
 * This file sets up the isolated testing environment:
 * 1. Loads configuration from .env.testing
 * 2. Auto-provisions the test database + schema
 * 3. Loads the PSR-4 autoloader and config functions
 * 4. Registers the Tests\ namespace autoloader
 */

define('FINZO_ROOT', dirname(__DIR__));
define('FINZO_TESTING', true);

// ── 1. Load .env.testing ────────────────────────────────────────────────────
$envFile = FINZO_ROOT . '/.env.testing';
if (file_exists($envFile)) {
    $envConfig = parse_ini_file($envFile);
    foreach ($envConfig as $key => $value) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
    }
} else {
    echo "[Test Bootstrap] FATAL: .env.testing file is missing.\n";
    exit(1);
}

// Mock SCRIPT_NAME to prevent warnings in routing logic
$_SERVER['SCRIPT_NAME'] = '/index.php';

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
    $dbName = getenv('DB_NAME') ?: 'finzo_test_db';

    $provisionPdo = new TestPDO("mysql:host={$dbHost};port=3306;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    // Create DB and switch context
    $provisionPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $provisionPdo->exec("USE `{$dbName}`");

    // Always re-import if schema_version marker table is missing
    $versionCheck = $provisionPdo->query("SHOW TABLES LIKE '_test_schema_version'");
    $needsImport = ($versionCheck->rowCount() === 0);

    if ($needsImport) {
        $provisionPdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $tables = $provisionPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $provisionPdo->exec("DROP TABLE IF EXISTS `$table`");
        }
        $provisionPdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        // Use Migrator to deploy the schema
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
        echo "[Test Bootstrap] Test database schema fully imported.\n";
    }

    // Assign to global $pdo so Database::getInstance() finds it
    global $pdo;
    $pdo = $provisionPdo;

} catch (PDOException $e) {
    echo "[Test Bootstrap] FATAL: Could not provision test database: " . $e->getMessage() . "\n";
    exit(1);
}

// ── 3. Load PSR-4 autoloader and core config/functions ──────────────────────
// Load app namespace
spl_autoload_register(function ($class) {
    $prefix  = 'App\\';
    $baseDir = FINZO_ROOT . '/app/';
    $len     = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

// Load config constants
require_once FINZO_ROOT . '/config/config.php';
require_once FINZO_ROOT . '/config/functions.php';
require_once FINZO_ROOT . '/vendor/autoload.php';

// ── 4. Register Tests\ namespace autoloader ──────────────────────────────────
spl_autoload_register(function ($class) {
    $prefix  = 'Tests\\';
    $baseDir = FINZO_ROOT . '/tests/';
    $len     = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});
