<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT id, admin_id, admin_role FROM admin_users");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n---\n";

$stmt = $pdo->query("DESCRIBE admin_lock_log");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
