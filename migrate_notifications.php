<?php
require 'config/db.php';

try {
    // 1. Add customer_id column to notifications
    $pdo->exec("ALTER TABLE notifications ADD COLUMN customer_id VARCHAR(20) AFTER id");
    
    // 2. Populate customer_id
    $pdo->exec("UPDATE notifications n JOIN users u ON n.user_id = u.id SET n.customer_id = u.customer_id");
    
    // 3. Drop FK and old column
    $pdo->exec("ALTER TABLE notifications DROP FOREIGN KEY fk_notification_user");
    $pdo->exec("ALTER TABLE notifications DROP COLUMN user_id");
    
    // 4. Rename customer_id to user_id
    $pdo->exec("ALTER TABLE notifications CHANGE COLUMN customer_id user_id VARCHAR(20) NOT NULL");
    
    // 5. Re-add FK constraint to users(customer_id)
    $pdo->exec("ALTER TABLE notifications ADD CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(customer_id) ON DELETE CASCADE");
    
    echo "Notifications table migrated to use customer_id successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
