<?php
namespace Tests\Factories;

use App\Shared\Core\Database;
use PDO;

class AdminFactory {
    
    /**
     * Create a mock admin in the database.
     * Returns the integer id.
     */
    public static function createAdmin(array $overrides = []): int {
        $db = Database::getInstance();
        
        $adminId = $overrides['admin_id'] ?? 'ADM' . rand(100000, 999999);
        $email = $overrides['email'] ?? strtolower($adminId) . '@example.com';
        $password = password_hash($overrides['password'] ?? 'Admin@123', PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO admin_users (admin_id, full_name, email, password_hash, admin_role, access_level, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $adminId,
            $overrides['full_name'] ?? 'Test Admin',
            $email,
            $password,
            $overrides['admin_role'] ?? 'admin',
            $overrides['access_level'] ?? 'full',
            $overrides['status'] ?? 'active'
        ]);
        
        return (int)$db->lastInsertId();
    }
}
