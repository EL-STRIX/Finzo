<?php
namespace Tests\Feature\Auth;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;

class RegisterTest extends TestCase {
    
    public function testRegisterSuccess() {
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'full_name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'phone' => '9876543210',
            'password' => 'SecurePass123!',
            'confirm_password' => 'SecurePass123!',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/register.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(201, $response['status']);
            $this->assertTrue($response['success']);
            
            // Check session
            $this->assertTrue(isset($_SESSION['user_id']));
            $this->assertEquals('customer', $_SESSION['role']);
            $this->assertEquals('johndoe@example.com', $_SESSION['user_email']);
            
            // Verify database
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute(['johndoe@example.com']);
            $this->assertEquals(1, $stmt->fetchColumn());
        }
    }

    public function testRegisterFailsEmailAlreadyExists() {
        UserFactory::createCustomer([
            'email' => 'existing@example.com'
        ]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'full_name' => 'Jane Doe',
            'email' => 'existing@example.com',
            'phone' => '9876543211',
            'password' => 'SecurePass123!',
            'confirm_password' => 'SecurePass123!',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/register.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(409, $response['status']);
            $this->assertFalse($response['success']);
            $this->assertEquals('This email is already registered', $response['errors']['email']);
        }
    }

    public function testRegisterFailsPasswordsDoNotMatch() {
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '9876543211',
            'password' => 'SecurePass123!',
            'confirm_password' => 'DifferentPass123!',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/register.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(422, $response['status']);
            $this->assertFalse($response['success']);
            $this->assertEquals('Passwords do not match', $response['errors']['confirm_password']);
        }
    }

    public function testRegisterFailsInvalidEmailAndPhone() {
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'full_name' => 'Jane Doe',
            'email' => 'not-an-email',
            'phone' => '123',
            'password' => 'SecurePass123!',
            'confirm_password' => 'SecurePass123!',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/register.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(422, $response['status']);
            $this->assertFalse($response['success']);
            $this->assertArrayHasKey('email', $response['errors']);
            $this->assertArrayHasKey('phone', $response['errors']);
        }
    }
}
