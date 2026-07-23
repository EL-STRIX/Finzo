<?php
namespace Tests\Factories;

use App\Shared\Core\Database;
use PDO;

class UserFactory {
    
    /**
     * Create a mock customer in the database.
     * Returns the customer_id string.
     */
    public static function createCustomer(array $overrides = []): string {
        $db = Database::getInstance();
        
        $customerId = $overrides['customer_id'] ?? 'CUST' . rand(100000, 999999);
        $email = $overrides['email'] ?? strtolower($customerId) . '@example.com';
        $phone = $overrides['phone'] ?? '9' . rand(100000000, 999999999);
        $password = password_hash($overrides['password'] ?? 'Password123!', PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (customer_id, full_name, email, phone, password_hash, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $customerId,
            $overrides['full_name'] ?? 'Test Customer',
            $email,
            $phone,
            $password,
            $overrides['status'] ?? 'active',
        ]);

        // Apply any optional overrides like locked_until
        if (isset($overrides['locked_until'])) {
            $db->prepare("UPDATE users SET locked_until = ? WHERE customer_id = ?")
               ->execute([$overrides['locked_until'], $customerId]);
        }
        
        return $customerId;
    }
    
    /**
     * Create an account for a customer
     */
    public static function createAccount(string $customerId, array $overrides = []): string {
        $db = Database::getInstance();
        
        $accountNo = $overrides['account_no'] ?? 'FNZ' . date('Y') . rand(10000, 99999);
        // Default PIN is 1234 — this is the bcrypt hash of '1234'
        $txnPinHash = $overrides['txn_pin_hash'] ?? password_hash('1234', PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO accounts (user_id, account_no, account_type, balance, status, daily_limit, txn_pin_hash) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $customerId,
            $accountNo,
            $overrides['account_type'] ?? 'savings',
            $overrides['balance'] ?? 5000.00,
            $overrides['status'] ?? 'active',
            $overrides['daily_limit'] ?? 100000.00,
            $txnPinHash,
        ]);
        
        return $accountNo;
    }
    
    public static function createAdmin(array $overrides = []): string {
        $db = Database::getInstance();
        $adminId = $overrides['admin_id'] ?? 'ADM' . rand(100, 999);
        $email = $overrides['email'] ?? strtolower($adminId) . '@example.com';
        $password = password_hash($overrides['password'] ?? 'Password123!', PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO admin_users (admin_id, full_name, email, password_hash, status, admin_role) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $db->prepare($sql)->execute([
            $adminId,
            $overrides['full_name'] ?? 'Test Admin',
            $email,
            $password,
            $overrides['status'] ?? 'active',
            $overrides['admin_role'] ?? 'super_admin',
        ]);
        
        return $adminId;
    }
    
    public static function createStaff(array $overrides = []): string {
        $db = Database::getInstance();
        
        // Ensure at least one branch exists
        $branchId = $db->query("SELECT id FROM branches LIMIT 1")->fetchColumn();
        if (!$branchId) {
            $db->prepare("INSERT INTO branches (name, branch_code, state) VALUES (?, ?, ?)")
               ->execute(['Main Branch', 'FINZ0000001', 'Default State']);
            $branchId = $db->lastInsertId();
        }
        
        $staffId = $overrides['staff_id'] ?? 'EMP' . rand(100, 999);
        $email = $overrides['email'] ?? strtolower($staffId) . '@example.com';
        $password = password_hash($overrides['password'] ?? 'Password123!', PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO staff (staff_id, branch_id, full_name, personal_email, phone, password_hash, status, designation) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $db->prepare($sql)->execute([
            $staffId,
            $branchId,
            $overrides['full_name'] ?? 'Test Staff',
            $email,
            $overrides['phone'] ?? '8' . rand(100000000, 999999999),
            $password,
            $overrides['status'] ?? 'active',
            $overrides['designation'] ?? 'Teller',
        ]);
        
        return $staffId;
    }
}
