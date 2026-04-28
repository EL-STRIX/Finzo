<?php
require_once __DIR__ . '/config/db.php';
try {
    $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS auto_unlock_at TIMESTAMP NULL");
    echo "<h1>SUCCESS: Column auto_unlock_at added.</h1>";
} catch (Exception $e) {
    echo "<h1>ERROR: " . $e->getMessage() . "</h1>";
}
