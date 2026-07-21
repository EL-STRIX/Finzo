<?php

class DevSeeder
{
    public function run(PDO $db): void
    {
        $db->exec("
            INSERT IGNORE INTO users (customer_id, full_name, email, phone, password_hash, status) VALUES 
            ('FNZ-USR-000001', 'John Doe', 'john@example.com', '9876543210', '" . password_hash('User@123', PASSWORD_BCRYPT) . "', 'active');
        ");
        
        $db->exec("
            INSERT IGNORE INTO accounts (account_no, user_id, branch_id, account_type, balance, daily_limit, status) VALUES 
            ('FNZ0000001001', 'FNZ-USR-000001', 1, 'savings', 50000.00, 10000.00, 'active');
        ");
        
        $db->exec("
            INSERT IGNORE INTO staff (staff_id, full_name, email, phone, branch_id, role, password_hash, status) VALUES 
            ('STF001', 'Jane Smith', 'jane@finzo.com', '9876543211', 1, 'manager', '" . password_hash('Staff@123', PASSWORD_BCRYPT) . "', 'active');
        ");

        echo "Development seeds injected successfully.\n";
    }
}
