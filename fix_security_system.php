<?php
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/db.php';

echo "<h2>Finzo Admin Security Migration</h2>";

// 1. Run Migration
try {
    $sql = file_get_contents(__DIR__ . '/database/update_admin_lock_schema.sql');
    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    $queries = explode(';', $sql);
    
    foreach ($queries as $q) {
        $q = trim($q);
        if ($q) {
            $pdo->exec($q);
            echo "<p style='color:green'>Executed: " . htmlspecialchars(substr($q, 0, 50)) . "...</p>";
        }
    }
    echo "<p><b>Migration completed successfully.</b></p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Migration Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 2. Check Users
echo "<h3>User Verification</h3>";
try {
    $stmt = $pdo->query("SELECT email FROM admin_users");
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Current Admins: <ul>";
    foreach ($users as $email) {
        echo "<li>" . htmlspecialchars($email) . "</li>";
    }
    echo "</ul>";
    
    if (!in_array('sujaypaul892@gmail.com', $users)) {
        echo "<p style='color:orange'>Warning: 'sujaypaul892@gmail.com' not found! Creating it now with password 'Admin@123'...</p>";
        $hash = password_hash('Admin@123', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO admin_users (full_name, email, password_hash, status, admin_role) VALUES (?, ?, ?, 'active', 'super_admin')")
            ->execute(['Sujay Paul', 'sujaypaul892@gmail.com', $hash]);
        echo "<p style='color:green'>User created successfully.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>User Check Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
