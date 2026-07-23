<?php
namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Infrastructure\Repositories\AuthRepository;
use PDO;

class AuthRepositoryTest extends TestCase {
    
    private AuthRepository $repository;
    private PDO $pdo;

    protected function setUp(): void {
        parent::setUp();
        
        global $pdo;
        $this->pdo = $pdo;
        
        $this->pdo->beginTransaction();
        $this->repository = new AuthRepository($this->pdo);
    }
    
    protected function tearDown(): void {
        $this->pdo->rollBack();
        parent::tearDown();
    }
    
    public function testFindUserByIdentity() {
        // Insert a dummy user
        $stmt = $this->pdo->prepare("INSERT INTO users (customer_id, full_name, email, phone, password_hash, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['CUST999', 'Test User', 'testauth@test.com', '9999999999', 'hash', 'active']);
        
        $user = $this->repository->findUserByIdentity('testauth@test.com');
        
        $this->assertNotFalse($user, 'User should be found by email');
        $this->assertEquals('testauth@test.com', $user['email']);
        $this->assertEquals('CUST999', $user['customer_id']);
        
        // Test with phone
        $userByPhone = $this->repository->findUserByPhone('9999999999');
        $this->assertNotFalse($userByPhone, 'User should be found by phone');
        $this->assertArrayHasKey('id', $userByPhone);
    }

    public function testUpdateFailedAttempts() {
        // Insert a dummy user
        $stmt = $this->pdo->prepare("INSERT INTO users (customer_id, full_name, email, phone, password_hash, status, failed_attempts) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['CUST998', 'Test User 2', 'testauth2@test.com', '9999999998', 'hash', 'active', 0]);
        $userId = $this->pdo->lastInsertId();
        
        // Update to 1
        $this->repository->updateFailedAttempts($userId, 1);
        
        $stmt = $this->pdo->prepare("SELECT failed_attempts FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $attempts = $stmt->fetchColumn();
        
        $this->assertEquals(1, $attempts);
        
        // Reset to 0
        $this->repository->updateFailedAttempts($userId, 0);
        
        $stmt->execute([$userId]);
        $attempts = $stmt->fetchColumn();
        
        $this->assertEquals(0, $attempts);
    }

    public function testUpdateSuccessfulLogin() {
        $stmt = $this->pdo->prepare("INSERT INTO users (customer_id, full_name, email, phone, password_hash, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['CUST997', 'Test User 3', 'testauth3@test.com', '9999999997', 'hash', 'active']);
        $userId = $this->pdo->lastInsertId();
        
        // Ensure no last_login_at yet
        $stmtCheck = $this->pdo->prepare("SELECT last_login_at FROM users WHERE id = ?");
        $stmtCheck->execute([$userId]);
        $lastLogin = $stmtCheck->fetchColumn();
        $this->assertNull($lastLogin);
        
        // Update login
        $this->repository->updateSuccessfulLogin($userId, '127.0.0.1');
        
        $stmtCheck->execute([$userId]);
        $lastLoginAfter = $stmtCheck->fetchColumn();
        $this->assertNotNull($lastLoginAfter);
    }
}
