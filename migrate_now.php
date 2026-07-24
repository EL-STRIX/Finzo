<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/database/Migrator.php';
use Database\Migrator;

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $migrator = new Migrator($pdo, __DIR__ . '/database/migrations', __DIR__ . '/database/seeds');
    $migrator->migrate();
    echo "Done migrating.\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
