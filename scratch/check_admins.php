<?php
include 'config/db.php';
$stmt = $pdo->prepare("SELECT email FROM admin_users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Admins found: " . implode(", ", $users) . "\n";
