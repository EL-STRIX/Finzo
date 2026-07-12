<?php
namespace App\Core;

use PDO;

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        // We rely on the legacy db.php which initialized $pdo in global scope
        global $pdo;
        if (!$pdo) {
            // Fallback initialization if global is missing
            require_once dirname(__DIR__, 2) . '/config/db.php';
        }
        $this->pdo = $pdo;
    }

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
