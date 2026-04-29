<?php
require_once __DIR__ . '/../config/db.php';
echo "BRANCHES:\n";
$stmt = $pdo->query("DESCRIBE branches");
print_r($stmt->fetchAll());
echo "\nTRANSACTIONS:\n";
$stmt = $pdo->query("DESCRIBE transactions");
print_r($stmt->fetchAll());
