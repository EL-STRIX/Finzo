<?php
// Manual connection to avoid config.php side effects
$host = 'localhost';
$db   = 'finzo_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected to database.\n";

    // 1. Add the column
    // MySQL 5.7+ supports ADD COLUMN IF NOT EXISTS in some contexts, but usually it's safer to check first.
    $check = $pdo->query("SHOW COLUMNS FROM admin_users LIKE 'admin_id'")->fetch();
    if (!$check) {
        $pdo->exec("ALTER TABLE admin_users ADD COLUMN admin_id VARCHAR(20) AFTER id");
        echo "Column 'admin_id' added.\n";
    } else {
        echo "Column 'admin_id' already exists.\n";
    }

    // 2. Fetch and update
    $stmt = $pdo->query("SELECT id, admin_role FROM admin_users ORDER BY (admin_role = 'super_admin') DESC, created_at ASC");
    $admins = $stmt->fetchAll();

    $counter = 1;
    $updateStmt = $pdo->prepare("UPDATE admin_users SET admin_id = ? WHERE id = ?");

    foreach ($admins as $admin) {
        $formattedId = 'ADM' . str_pad($counter, 3, '0', STR_PAD_LEFT);
        $updateStmt->execute([$formattedId, $admin['id']]);
        echo "Assigned $formattedId to Admin ID {$admin['id']} ({$admin['admin_role']})\n";
        $counter++;
    }

    echo "Migration completed.\n";

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
?>
