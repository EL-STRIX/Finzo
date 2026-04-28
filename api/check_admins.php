<?php
require_once dirname(__DIR__) . '/config/functions.php';
require_once dirname(__DIR__) . '/config/db.php';

echo "--- DATABASE STATUS ---\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM admin_users");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in admin_users: " . implode(", ", $cols) . "\n\n";

    $stmt = $pdo->query("SELECT email, password_hash, status, account_locked FROM admin_users");
    $users = $stmt->fetchAll();
    echo "Admins found:\n";
    foreach ($users as $u) {
        echo "- Email: " . $u['email'] . " | Status: " . $u['status'] . " | Locked: " . $u['account_locked'] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
