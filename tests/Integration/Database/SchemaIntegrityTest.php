<?php
namespace Tests\Integration\Database;

use Tests\TestCase;
use App\Shared\Core\Database;
use Tests\Factories\UserFactory;
use PDOException;

class SchemaIntegrityTest extends TestCase {
    
    public function testForeignKeyConstraintPreventsOrphanedAccounts() {
        $db = Database::getInstance();
        
        // Attempt to insert an account with a non-existent customer_id
        $this->expectException(PDOException::class);
        
        $stmt = $db->prepare("INSERT INTO accounts (user_id, account_no, account_type) VALUES (?, ?, ?)");
        $stmt->execute(['NON_EXISTENT_CUST', 'FNZ9999999', 'savings']);
    }

    public function testUniqueConstraintOnEmail() {
        $db = Database::getInstance();
        
        // Create a real customer
        UserFactory::createCustomer(['email' => 'duplicate@example.com']);
        
        // Attempt to insert another customer with the same email
        $this->expectException(PDOException::class);
        $this->expectExceptionCode('23000'); // SQLSTATE for Integrity constraint violation
        
        $stmt = $db->prepare("INSERT INTO users (customer_id, full_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['CUST9999999', 'Duplicate User', 'duplicate@example.com', '9999999999', 'hash']);
    }

    public function testDefaultValuesAreAppliedCorrectly() {
        $db = Database::getInstance();
        
        // Create a user manually to check defaults
        $stmt = $db->prepare("INSERT INTO users (customer_id, full_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['CUST8888888', 'Default User', 'default@example.com', '8888888888', 'hash']);
        $userId = $db->lastInsertId();
        
        $stmt = $db->prepare("SELECT status, country, failed_attempts FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $this->assertEquals('pending', $row['status']);
        $this->assertEquals('India', $row['country']);
        $this->assertEquals(0, $row['failed_attempts']);
    }
}
