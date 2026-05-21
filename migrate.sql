SET FOREIGN_KEY_CHECKS = 0;

-- Drop foreign key and old user_id column if they exist, to start clean
ALTER TABLE notifications DROP FOREIGN KEY IF EXISTS fk_notification_user;
-- If the column customer_id doesn't exist, we must add it. If it does (from partial failure), we use it.
-- Let's just create a new table and copy data to be safe and robust!

CREATE TABLE notifications_new (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL,
    type ENUM('transaction', 'security', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(customer_id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert matching records into the new table
INSERT INTO notifications_new (id, user_id, type, title, message, is_read, created_at)
SELECT n.id, u.customer_id, n.type, n.title, n.message, n.is_read, n.created_at
FROM notifications n
JOIN users u ON n.user_id = u.id OR n.user_id = u.customer_id;

-- Swap tables
RENAME TABLE notifications TO notifications_old, notifications_new TO notifications;

SET FOREIGN_KEY_CHECKS = 1;
