<?php

class CreateSupportTables
{
    public function up(PDO $pdo)
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS support_tickets (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            ticket_no VARCHAR(30) NOT NULL UNIQUE,
            user_id INT(11) NOT NULL,
            category VARCHAR(50) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
            status ENUM('open','in_progress','resolved','closed') DEFAULT 'open',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_support_user_id (user_id),
            INDEX idx_support_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS support_ticket_messages (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT(11) NOT NULL,
            sender_type ENUM('user','staff','admin') NOT NULL,
            sender_id INT(11) NOT NULL,
            message TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ticket_msg_ticket_id (ticket_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($sql);
    }

    public function down(PDO $pdo)
    {
        $pdo->exec("DROP TABLE IF EXISTS support_ticket_messages");
        $pdo->exec("DROP TABLE IF EXISTS support_tickets");
    }
}
