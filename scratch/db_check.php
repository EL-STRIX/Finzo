<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=finzo_db;charset=utf8mb4', 'root', '');
    $stmt = $pdo->query('SHOW COLUMNS FROM admin_users');
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo implode("\n", $cols);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
