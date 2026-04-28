<?php
require_once __DIR__ . '/config/db.php';
$stmt = $pdo->query("DESCRIBE admin_users");
print_r($stmt->fetchAll());
