<?php
namespace Tests\Integration;

use Tests\TestCase;
use Tests\Factories\UserFactory;
use App\Shared\Core\Database;

class DatabaseIntegrationTest extends TestCase {
    
    public function testDatabaseConnectionAndCrud() {
        $db = Database::getInstance();
        
        $this->assertInstanceOf(\PDO::class, $db);
        
        // Insert
        $stmt = $db->prepare("INSERT INTO branches (name, branch_code, state) VALUES (?, ?, ?)");
        $stmt->execute(['Crud Branch', 'CRUD001', 'Crud State']);
        $branchId = $db->lastInsertId();
        
        $this->assertGreaterThan(0, $branchId);
        
        // Read
        $stmt = $db->prepare("SELECT name FROM branches WHERE id = ?");
        $stmt->execute([$branchId]);
        $name = $stmt->fetchColumn();
        $this->assertEquals('Crud Branch', $name);
        
        // Update
        $stmt = $db->prepare("UPDATE branches SET state = ? WHERE id = ?");
        $stmt->execute(['Updated State', $branchId]);
        
        $stmt = $db->prepare("SELECT state FROM branches WHERE id = ?");
        $stmt->execute([$branchId]);
        $state = $stmt->fetchColumn();
        $this->assertEquals('Updated State', $state);
        
        // Delete
        $stmt = $db->prepare("DELETE FROM branches WHERE id = ?");
        $stmt->execute([$branchId]);
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM branches WHERE id = ?");
        $stmt->execute([$branchId]);
        $count = $stmt->fetchColumn();
        
        $this->assertEquals(0, $count);
    }
    
    public function testUserAndAccountCreationFlow() {
        $db = Database::getInstance();
        
        // Use factory to create customer and account
        $customerId = UserFactory::createCustomer(['full_name' => 'Integration User']);
        $this->assertStringStartsWith('CUST', $customerId);
        
        $accountNo = UserFactory::createAccount($customerId, ['balance' => 15000.50]);
        $this->assertStringStartsWith('FNZ', $accountNo);
        
        // Verify the database state directly
        $stmt = $db->prepare("SELECT u.full_name, a.balance FROM users u JOIN accounts a ON u.customer_id = a.user_id WHERE u.customer_id = ?");
        $stmt->execute([$customerId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $this->assertIsArray($result);
        $this->assertEquals('Integration User', $result['full_name']);
        $this->assertEquals(15000.50, $result['balance']);
    }
}
