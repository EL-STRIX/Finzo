<?php
require_once __DIR__ . '/../config/db.php';

try {
    $pdo->exec("ALTER TABLE admin_users ADD COLUMN temp_password_expiry TIMESTAMP NULL DEFAULT NULL AFTER failed_attempts");
    echo "Successfully added temp_password_expiry column to admin_users table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column temp_password_expiry already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
