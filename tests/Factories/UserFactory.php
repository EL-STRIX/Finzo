<?php
namespace Tests\Factories;

use App\Core\Database;
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
        
        $sql = "INSERT INTO users (customer_id, full_name, email, phone, password_hash, status, kyc_status, id_type, id_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $customerId,
            $overrides['full_name'] ?? 'Test Customer',
            $email,
            $phone,
            $password,
            $overrides['status'] ?? 'active',
            $overrides['kyc_status'] ?? 'approved',
            $overrides['id_type'] ?? 'aadhaar',
            $overrides['id_number'] ?? '123456789012'
        ]);
        
        return $customerId;
    }
    
    /**
     * Create an account for a customer
     */
    public static function createAccount(string $customerId, array $overrides = []): string {
        $db = Database::getInstance();
        
        $accountNo = $overrides['account_no'] ?? '100' . rand(1000000, 9999999);
        
        $sql = "INSERT INTO accounts (user_id, account_no, account_type, balance, status, daily_limit) 
                VALUES (?, ?, ?, ?, ?, ?)";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $customerId,
            $accountNo,
            $overrides['account_type'] ?? 'savings',
            $overrides['balance'] ?? 5000.00,
            $overrides['status'] ?? 'active',
            $overrides['daily_limit'] ?? 100000.00
        ]);
        
        return $accountNo;
    }
}
