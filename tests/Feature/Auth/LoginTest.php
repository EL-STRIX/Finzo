<?php
namespace Tests\Feature\Auth;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;
use App\Core\Database;

class LoginTest extends TestCase {
    
    public function testLoginSuccessWithEmail() {
        // Create user
        $password = 'Secret123!';
        $customerId = UserFactory::createCustomer([
            'email' => 'login_user@example.com',
            'password' => $password
        ]);
        UserFactory::createAccount($customerId);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'identity' => 'login_user@example.com',
            'password' => $password,
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/login.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(200, $response['status']);
            $this->assertTrue($response['success']);
            $this->assertEquals('Login successful', $response['message']);
            
            // Check session for logged-in state
            $this->assertTrue(isset($_SESSION['user_id']));
            $this->assertEquals('customer', $_SESSION['role']);
        }
    }

    public function testLoginFailureWithInvalidPassword() {
        $customerId = UserFactory::createCustomer([
            'email' => 'wrong_pass@example.com',
            'password' => 'Correct123!'
        ]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'identity' => 'wrong_pass@example.com',
            'password' => 'Wrong123!',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/login.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(401, $response['status']);
            $this->assertFalse($response['success']);
        }
    }
    
    public function testLoginFailsWithoutCsrfToken() {
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'identity' => 'test@example.com',
            'password' => 'password123'
            // Missing csrf_token
        ];
        
        try {
            RequestHelper::postJson('Auth/login.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(403, $response['status']);
            $this->assertFalse($response['success']);
        }
    }

    public function testLoginFailsWithEmptyFields() {
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'identity' => '',
            'password' => '',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/login.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(422, $response['status']);
            $this->assertFalse($response['success']);
        }
    }
}
