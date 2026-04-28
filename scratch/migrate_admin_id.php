<?php
require_once 'config/db.php';

try {
    // 1. Add the column if it doesn't exist
    $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS admin_id VARCHAR(20) AFTER id");
    echo "Column 'admin_id' added (or already exists).\n";

    // 2. Fetch all admins ordered by created_at or id to assign IDs
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

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
