<?php
require_once __DIR__ . '/../config/db.php';
$sql = "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_NAME = 'staff'";
$stmt = $pdo->query($sql);
$deps = $stmt->fetchAll();
foreach ($deps as $dep) {
    echo "Table: " . $dep['TABLE_NAME'] . " Column: " . $dep['COLUMN_NAME'] . "\n";
}
