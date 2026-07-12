<?php
namespace Tests\Unit\Services;

use Tests\TestCase;
use Tests\Factories\UserFactory;
use App\Services\AuthService;
use App\Repositories\AuthRepository;

class AuthServiceTest extends TestCase {
    
    private AuthService $authService;
    
    protected function setUp(): void {
        parent::setUp();
        $this->authService = new AuthService(new AuthRepository());
    }

    public function test_loginUser_with_valid_credentials() {
        // Arrange
        $customerId = UserFactory::createCustomer([
            'email' => 'valid@test.com',
            'password' => 'CorrectPassword123'
        ]);
        
        // Act
        // loginUser does not return anything on success, it sets $_SESSION
        $this->authService->loginUser('valid@test.com', 'CorrectPassword123');
        
        // Assert
        $this->assertEquals($customerId, $_SESSION['customer_id']);
        $this->assertTrue($_SESSION['customer_logged_in']);
    }

    public function test_loginUser_with_invalid_credentials_throws_json_exception() {
        // Arrange
        UserFactory::createCustomer([
            'email' => 'valid@test.com',
            'password' => 'CorrectPassword123'
        ]);
        
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid email or password');
        
        // Act
        $this->authService->loginUser('valid@test.com', 'WrongPassword123');
    }

    public function test_loginUser_with_locked_account() {
        // Arrange
        UserFactory::createCustomer([
            'email' => 'locked@test.com',
            'locked_until' => date('Y-m-d H:i:s', time() + 3600) // Locked for 1 hour
        ]);
        
        // To properly test this, we should inject the locked_until directly into the DB.
        // Wait, UserFactory doesn't support locked_until out of the box, let's update it or run a manual query.
        $this->db->exec("UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = 'locked@test.com'");

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Account locked');
        
        // Act
        $this->authService->loginUser('locked@test.com', 'AnyPassword');
    }
}
