<?php
require_once __DIR__ . '/config/config.php';
ini_set('display_errors', '1');
error_reporting(E_ALL);
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

echo "Database connection check:\n";
if (isset($pdo)) {
    echo "PDO is set!\n";
} else {
    echo "PDO is NOT set!\n";
    exit;
}

try {
    $pdo->beginTransaction();
    $staff_id_str = generate_staff_id($pdo);
    echo "Generated staff ID: $staff_id_str\n";
    $pdo->rollBack();
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
