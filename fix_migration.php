<?php
require_once __DIR__ . '/config/db.php';
try {
    $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS auto_unlock_at TIMESTAMP NULL");
    echo "Column auto_unlock_at added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
