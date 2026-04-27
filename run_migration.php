<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
try {
    $pdo = new PDO('mysql:host=localhost;dbname=finzo_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Disable foreign keys temporarily
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    
    $sql = file_get_contents(__DIR__ . '/database/update_admin_schema.sql');
    $pdo->exec($sql);
    
    // Enable foreign keys
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    echo "Schema updated successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
