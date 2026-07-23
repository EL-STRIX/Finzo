<?php

class SystemSeeder
{
    public function run(PDO $db): void
    {
        $db->exec("
            INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_category, description) VALUES 
            ('maintenance_mode', 'false', 'application', 'Enable or disable maintenance mode'),
            ('default_currency', 'INR', 'application', 'System default currency'),
            ('max_failed_login_attempts', '5', 'security', 'Number of failed logins before lockout'),
            ('customer_lockout_duration', '1800', 'security', 'Lockout duration in seconds'),
            ('min_initial_deposit', '500', 'banking', 'Minimum deposit for new accounts'),
            ('daily_transfer_limit', '10000', 'banking', 'Daily transfer limit for standard accounts');
        ");

        $db->exec("
            INSERT IGNORE INTO branches (branch_code, name, state, type, status) VALUES 
            ('BR001', 'Head Office', 'Maharashtra', 'system', 'active');
        ");
        
        $db->exec("
            INSERT IGNORE INTO admin_users (admin_id, password_hash, full_name, email, admin_role, status, security_question_set) VALUES 
            ('ADM001', '" . password_hash('Admin@123', PASSWORD_BCRYPT) . "', 'Super Admin', 'admin@finzo.com', 'super_admin', 'active', 0);
        ");

        echo "System seeds injected successfully.\n";
    }
}
