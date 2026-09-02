<?php

class SystemSeeder
{
    public function run(PDO $db): void
    {
        $db->exec("
            INSERT INTO system_settings (setting_key, setting_value, setting_type, setting_category, label, description) VALUES 
            ('min_initial_deposit', '500', 'number', 'banking', 'Minimum Initial Deposit (₹)', 'Minimum balance required when opening a new savings account'),
            ('daily_transfer_limit', '10000', 'number', 'banking', 'Daily Transfer Limit (₹)', 'Maximum daily transaction exposure for standard accounts'),
            ('savings_interest_rate', '4.25', 'number', 'banking', 'Savings Interest Rate (% p.a.)', 'Base annual interest rate credited to active savings accounts'),
            ('max_failed_login_attempts', '5', 'number', 'security', 'Max Failed Login Attempts', 'Consecutive incorrect password attempts before account lockout'),
            ('customer_lockout_duration', '1800', 'number', 'security', 'Lockout Duration (Seconds)', 'Duration a locked account remains in cooldown before retry'),
            ('session_timeout_minutes', '30', 'number', 'security', 'Admin Session Timeout (Minutes)', 'Automatic logout duration for inactive administrative sessions'),
            ('maintenance_mode', 'false', 'boolean', 'application', 'Maintenance Mode', 'Temporarily restrict non-administrative access for system maintenance'),
            ('default_currency', 'INR', 'string', 'application', 'Default Currency', 'ISO currency symbol used across ledger and reporting'),
            ('allow_user_registration', 'true', 'boolean', 'application', 'Public User Registration', 'Allow new customer onboarding applications from the public portal'),
            ('auto_interest_calculation', 'true', 'boolean', 'operational', 'Automated Interest Calculation', 'Enable background worker to accrue and settle monthly interest'),
            ('audit_log_retention_days', '90', 'number', 'operational', 'Audit Log Retention (Days)', 'Number of days system audit trails and logs are preserved')
            ON DUPLICATE KEY UPDATE 
                label = VALUES(label),
                setting_type = VALUES(setting_type),
                setting_category = VALUES(setting_category),
                description = VALUES(description);
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
