<?php
require_once __DIR__ . '/../config/db.php';

$sql_file = __DIR__ . '/update_staff_schema.sql';
$sql = file_get_contents($sql_file);

try {
    $pdo->exec($sql);
    echo "Staff schema updated successfully.\n";
} catch (PDOException $e) {
    echo "Error updating staff schema: " . $e->getMessage() . "\n";
}
