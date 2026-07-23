<?php

namespace App\Shared\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        global $pdo;
        if (isset($pdo) && $pdo instanceof PDO) {
            // Use the globally configured connection (set by config/db.php)
            $this->pdo = $pdo;
            return;
        }

        // Fallback: build a direct connection using env vars (needed for testing)
        $host = getenv('DB_HOST') ?: 'localhost';
        $name = getenv('DB_NAME') ?: 'finzo_db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

        try {
            $dsn = "mysql:host={$host};port=3306;dbname={$name};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            // Also set the global so legacy code works
            } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    /**
     * Reset the singleton — used only in tests to get a fresh connection.
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}
