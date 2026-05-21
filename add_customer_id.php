<?php
require 'config/db.php';

try {
    // 1. Add column if not exists
    $pdo->exec("ALTER TABLE users ADD COLUMN customer_id VARCHAR(20) UNIQUE AFTER id");
    
    // 2. Backfill existing users
    $stmt = $pdo->query("SELECT id FROM users WHERE customer_id IS NULL");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updateStmt = $pdo->prepare("UPDATE users SET customer_id = ? WHERE id = ?");
    foreach ($users as $user) {
        $customer_id = 'FNZ-USR-' . str_pad($user['id'], 3, '0', STR_PAD_LEFT);
        $updateStmt->execute([$customer_id, $user['id']]);
    }
    
    echo "Success: customer_id added and backfilled.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        // If column already exists, just backfill
        $stmt = $pdo->query("SELECT id FROM users WHERE customer_id IS NULL");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updateStmt = $pdo->prepare("UPDATE users SET customer_id = ? WHERE id = ?");
        foreach ($users as $user) {
            $customer_id = 'FNZ-USR-' . str_pad($user['id'], 3, '0', STR_PAD_LEFT);
            $updateStmt->execute([$customer_id, $user['id']]);
        }
        echo "Success: customer_id backfilled (column already existed).";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
