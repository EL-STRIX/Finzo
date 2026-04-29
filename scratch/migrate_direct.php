<?php
// Direct connection to avoid include issues
$host = 'localhost';
$db   = 'finzo_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
     $pdo = new PDO($dsn, $user, $pass);
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $pdo->exec("ALTER TABLE admin_users ADD COLUMN temp_password_expiry TIMESTAMP NULL DEFAULT NULL AFTER failed_attempts");
     echo "Column added successfully.";
} catch (PDOException $e) {
     echo "Error: " . $e->getMessage();
}
