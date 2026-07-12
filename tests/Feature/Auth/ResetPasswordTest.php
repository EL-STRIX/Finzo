<?php
namespace Tests\Feature\Auth;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;

class ResetPasswordTest extends TestCase {

    public function testForgotPasswordSuccess() {
        $customerId = UserFactory::createCustomer([
            'email' => 'forgot@example.com'
        ]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'email' => 'forgot@example.com',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/forgot-password.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(200, $response['status']);
            $this->assertTrue($response['data']['success']);
            
            // Verify token in database
            $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE user_id = ?");
            $stmt->execute([$customerId]);
            $reset = $stmt->fetch();
            $this->assertNotEmpty($reset);
            $this->assertNotEmpty($reset['token']);
        }
    }

    public function testForgotPasswordUnknownEmail() {
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'email' => 'unknown@example.com',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/forgot-password.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(200, $response['status']);
            $this->assertTrue($response['data']['success']);
            $this->assertEquals('If an account with this email exists, a password reset link has been sent.', $response['data']['message']);
        }
    }
    
    public function testResetPasswordSuccess() {
        $customerId = UserFactory::createCustomer([
            'password' => 'OldPassword123!'
        ]);
        
        $token = 'valid_reset_token';
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        $stmt = $this->db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$customerId, $token, $expiresAt]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'token' => $token,
            'new_password' => 'NewPassword123!',
            'confirm_password' => 'NewPassword123!',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/reset-password.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(200, $response['status']);
            $this->assertTrue($response['data']['success']);
            
            // Verify password updated
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE customer_id = ?");
            $stmt->execute([$customerId]);
            $hash = $stmt->fetchColumn();
            $this->assertTrue(password_verify('NewPassword123!', $hash));
            
            // Verify token deleted
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            $this->assertEquals(0, $stmt->fetchColumn());
        }
    }
    
    public function testResetPasswordExpiredToken() {
        $customerId = UserFactory::createCustomer();
        
        $token = 'expired_reset_token';
        $expiresAt = date('Y-m-d H:i:s', time() - 3600); // Expired
        $stmt = $this->db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$customerId, $token, $expiresAt]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'token' => $token,
            'new_password' => 'NewPassword123!',
            'confirm_password' => 'NewPassword123!',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/reset-password.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(400, $response['status']);
            $this->assertFalse($response['data']['success']);
            $this->assertStringContainsString('expired', $response['data']['message']);
        }
    }
    
    public function testResetPasswordFailsPasswordsDoNotMatch() {
        $customerId = UserFactory::createCustomer();
        
        $token = 'valid_reset_token2';
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        $stmt = $this->db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$customerId, $token, $expiresAt]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'token' => $token,
            'new_password' => 'NewPassword123!',
            'confirm_password' => 'DifferentPassword123!',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/reset-password.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(422, $response['status']);
            $this->assertFalse($response['data']['success']);
            $this->assertArrayHasKey('confirm_password', $response['data']['errors']);
        }
    }
}
