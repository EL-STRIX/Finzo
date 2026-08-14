<?php

class DevSeeder
{
    private array $firstNames = ['Rahul', 'Priya', 'Amit', 'Neha', 'Sanjay', 'Anjali', 'Vikram', 'Pooja', 'Rohan', 'Sneha', 'Arjun', 'Kavita', 'Karan', 'Divya', 'Deepak', 'Aarti'];
    private array $lastNames = ['Sharma', 'Verma', 'Gupta', 'Patel', 'Singh', 'Kumar', 'Joshi', 'Mehta', 'Rao', 'Deshmukh', 'Chopra', 'Nair'];
    private array $cities = ['Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai', 'Pune', 'Kolkata', 'Ahmedabad'];
    private array $states = ['Maharashtra', 'Delhi', 'Karnataka', 'Telangana', 'Tamil Nadu', 'Maharashtra', 'West Bengal', 'Gujarat'];
    private array $occupations = ['Software Engineer', 'Teacher', 'Doctor', 'Business Owner', 'Accountant', 'Student', 'Artist', 'Manager'];

    public function run(PDO $db): void
    {
        try {
            echo "Starting comprehensive development seed...\n";

            // 1. Branches
            echo "Seeding Branches...\n";
            $branches = [
                ['BR002', 'Mumbai Central', 'Maharashtra'],
                ['BR003', 'Connaught Place', 'Delhi'],
                ['BR004', 'Koramangala', 'Karnataka']
            ];
            foreach ($branches as $b) {
                $db->exec("INSERT IGNORE INTO branches (branch_code, name, state, type, status) VALUES ('{$b[0]}', '{$b[1]}', '{$b[2]}', 'custom', 'active')");
            }

            // 2. Admins
            echo "Seeding Admins...\n";
            $pwd = password_hash('Admin@123', PASSWORD_BCRYPT);
            $db->exec("INSERT IGNORE INTO admin_users (admin_id, full_name, email, password_hash, admin_role, status, account_locked, lock_reason) VALUES 
                ('ADM002', 'Ravi Manager', 'ravi@finzo.com', '$pwd', 'admin', 'active', 0, NULL),
                ('ADM003', 'Sunil Moderator', 'sunil@finzo.com', '$pwd', 'moderator', 'active', 0, NULL),
                ('ADM004', 'Locked Admin', 'locked@finzo.com', '$pwd', 'admin', 'active', 1, 'Multiple failed login attempts'),
                ('ADM005', 'Suspended Admin', 'suspended@finzo.com', '$pwd', 'suspended', 'suspended', 0, NULL)");

            // 3. Staff
            echo "Seeding Staff...\n";
            $staffPwd = password_hash('Staff@123', PASSWORD_BCRYPT);
            for ($i = 1; $i <= 10; $i++) {
                $id = 'STF' . str_pad((string)$i, 3, '0', STR_PAD_LEFT);
                $fn = $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)];
                $branch_id = rand(1, 4); // BR001 to BR004
                $status = ($i === 10) ? 'suspended' : 'active';
                $email = strtolower(str_replace(' ', '.', $fn)) . $i . '@finzo.com';
                $db->exec("INSERT IGNORE INTO staff (staff_id, full_name, personal_email, phone, branch_id, designation, department, password_hash, status) VALUES 
                    ('$id', '$fn', '$email', '98765432" . str_pad((string)$i, 2, '0', STR_PAD_LEFT) . "', $branch_id, 'Teller', 'Teller Operations', '$staffPwd', '$status')");
            }

            // 4. Users & Accounts & KYC
            echo "Seeding Users, Accounts, KYC...\n";
            $userPwd = password_hash('User@123', PASSWORD_BCRYPT);
            $pinHash = password_hash('1234', PASSWORD_BCRYPT);
            
            $userTypes = ['active', 'active', 'active', 'under_review', 'suspended', 'rejected'];
            
            for ($i = 1; $i <= 30; $i++) {
                $cid = 'FNZ-USR-' . str_pad((string)$i, 6, '0', STR_PAD_LEFT);
                $fn = $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)];
                $email = strtolower(str_replace(' ', '.', $fn)) . $i . '@example.com';
                $phone = '9000000' . str_pad((string)$i, 3, '0', STR_PAD_LEFT);
                $status = $userTypes[array_rand($userTypes)];
                $is_deleted = ($i === 29 || $i === 30) ? 1 : 0;
                if ($is_deleted) $status = 'active'; // logically deleted but active
                
                $db->exec("INSERT INTO users (customer_id, full_name, email, phone, password_hash, status, is_deleted, dob, gender, occupation, city) VALUES 
                ('$cid', '$fn', '$email', '$phone', '$userPwd', '$status', $is_deleted, '1990-01-01', 'Male', 'Engineer', 'Mumbai')");
                    
                if ($status === 'active' && !$is_deleted) {
                    $accNo = 'FNZ000' . str_pad((string)$i, 6, '0', STR_PAD_LEFT);
                    $bal = rand(1000, 500000);
                    $db->exec("INSERT IGNORE INTO accounts (account_no, user_id, account_type, balance, txn_pin_hash, daily_limit, status, opened_date) VALUES 
                        ('$accNo', '$cid', 'savings', $bal, '$pinHash', 50000, 'active', '2025-01-01')");
                }
                
                if ($status === 'under_review') {
                    $appNo = 'KYC-APP-' . str_pad((string)$i, 6, '0', STR_PAD_LEFT);
                    $db->exec("INSERT IGNORE INTO kyc_applications (user_id, application_no, status, submitted_at) VALUES 
                        ('$cid', '$appNo', 'under_review', NOW())");
                }
            }
            
            // Locked user for admin-locked-accounts
            $db->exec("INSERT IGNORE INTO users (customer_id, full_name, email, phone, password_hash, status, failed_attempts, locked_until) VALUES 
                    ('FNZ-USR-000099', 'Locked User', 'lockeduser@example.com', '9999999999', '$userPwd', 'active', 5, DATE_ADD(NOW(), INTERVAL 1 DAY))");
            $db->exec("INSERT IGNORE INTO accounts (account_no, user_id, account_type, balance, txn_pin_hash, status) VALUES 
                        ('FNZ0000000099', 'FNZ-USR-000099', 'savings', 1000, '$pinHash', 'active')");

            // 5. Transactions
            echo "Seeding Transactions...\n";
            $stmt = $db->query("SELECT id FROM accounts WHERE status = 'active' LIMIT 10");
            $accIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($accIds) > 1) {
                for ($i = 1; $i <= 50; $i++) {
                    $txnId = 'TXN' . time() . rand(1000, 9999);
                    $from = $accIds[array_rand($accIds)];
                    $to = $accIds[array_rand($accIds)];
                    while ($from === $to) { $to = $accIds[array_rand($accIds)]; }
                    
                    $amount = rand(100, 10000);
                    $types = ['deposit', 'withdrawal', 'transfer'];
                    $type = $types[array_rand($types)];
                    
                    $fromSql = ($type === 'deposit') ? "NULL" : $from;
                    $toSql = ($type === 'withdrawal') ? "NULL" : $to;
                    $status = ($i % 10 === 0) ? 'failed' : 'completed';
                    
                    $db->exec("INSERT IGNORE INTO transactions (txn_id, from_account_id, to_account_id, type, amount, status, description) VALUES 
                        ('$txnId', $fromSql, $toSql, '$type', $amount, '$status', 'Demo $type')");
                }
            }

            // 6. Audit Logs
            echo "Seeding Audit Logs...\n";
            for ($i = 1; $i <= 20; $i++) {
                $evt = 'EVT-AUD-' . time() . rand(1000, 9999) . $i;
                $actions = ['login_success', 'login_failed', 'profile_update', 'password_change', 'account_lock'];
                $act = $actions[array_rand($actions)];
                $stat = ($act === 'login_failed' || $act === 'account_lock') ? 'warning' : 'success';
                
                $db->exec("INSERT IGNORE INTO audit_log (event_id, module, action_type, actor_id, actor_role, status, details) VALUES 
                    ('$evt', 'security', '$act', 'ADM001', 'admin', '$stat', 'Simulated audit event $act')");
            }

            // 7. Products (Loans & Cards)
            echo "Seeding Loan & Card Products...\n";
            $db->exec("INSERT IGNORE INTO loan_products (name, interest_rate, min_amount, max_amount, min_tenure_months, max_tenure_months, status) VALUES 
                ('Personal Loan', 10.5, 10000, 500000, 6, 60, 'active'),
                ('Home Loan', 8.5, 500000, 50000000, 12, 240, 'active'),
                ('Auto Loan', 9.5, 100000, 2000000, 12, 84, 'active')");
                
            $db->exec("INSERT IGNORE INTO card_products (name, type, network, daily_withdrawal_limit, daily_purchase_limit, status) VALUES 
            ('Platinum Debit', 'debit', 'visa', 100000, 200000, 'active'),
            ('Titanium Credit', 'credit', 'mastercard', 50000, 500000, 'active'),
            ('Virtual Online', 'virtual', 'rupay', 20000, 50000, 'active')");

            // 8. Loans & Cards instances
            $stmt = $db->query("SELECT id, customer_id FROM users WHERE status = 'active' AND is_deleted = 0 LIMIT 5");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $db->query("SELECT id FROM accounts WHERE status = 'active' LIMIT 5");
            $aIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($users) >= 5 && count($aIds) >= 5) {
                echo "Seeding Loans & Cards instances...\n";
                for ($i = 0; $i < 5; $i++) {
                    $uid = $users[$i]['id'];
                    $cid = $users[$i]['customer_id'];
                    $aid = $aIds[$i];
                    
                    // Loan
                    $db->exec("INSERT IGNORE INTO loan_applications (user_id, loan_product_id, amount, tenure_months, purpose, status) VALUES 
                        ($uid, 1, 100000, 24, 'Medical', 'approved')");
                    $appId = $db->lastInsertId();
                    if ($appId) {
                        $loanRef = 'L' . time() . rand(100, 999) . $i;
                        $db->exec("INSERT IGNORE INTO loans (loan_id_ref, user_id, loan_product_id, application_id, account_id, principal_amount, interest_rate, tenure_months, total_amount_payable, status) VALUES 
                            ('$loanRef', $uid, 1, $appId, $aid, 100000, 10.5, 24, 121000, 'active')");
                    }
                    
                    // Card
                    $cardNo = '4532' . rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999);
                    $db->exec("INSERT IGNORE INTO cards (card_number, user_id, account_id, card_product_id, name_on_card, expiry_date, cvv_hash, pin_hash, status) VALUES 
                        ('$cardNo', $uid, $aid, 1, 'TEST USER', '12/30', 'cvv', 'pin', 'active')");
                }
            }

            echo "Development seeds injected successfully.\n";
        } catch (PDOException $e) {
            echo "DATABASE SEED ERROR: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}
