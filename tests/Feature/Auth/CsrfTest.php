<?php
namespace Tests\Feature\Auth;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;

class CsrfTest extends TestCase {
    
    public function testGetCsrfToken() {
        try {
            RequestHelper::getJson('Auth/get-csrf-token.php', []);
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(200, $response['status']);
            $this->assertTrue($response['success']);
            $this->assertNotEmpty($response['csrf_token']);
            
            // Check session
            $this->assertEquals($response['csrf_token'], $_SESSION['csrf_token']);
        }
    }
    
    public function testCsrfValidationFailsWithInvalidToken() {
        $session = ['csrf_token' => 'valid_token_in_session'];
        $postData = [
            'identity' => 'test@example.com',
            'password' => 'password',
            'csrf_token' => 'wrong_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/login.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(403, $response['status']);
            $this->assertFalse($response['success']);
            $this->assertStringContainsString('CSRF', $response['message']);
        }
    }
    
    public function testCsrfValidationFailsWithMissingTokenInPost() {
        $session = ['csrf_token' => 'valid_token_in_session'];
        $postData = [
            'identity' => 'test@example.com',
            'password' => 'password'
            // no csrf_token
        ];
        
        try {
            RequestHelper::postJson('Auth/login.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(403, $response['status']);
            $this->assertFalse($response['success']);
            $this->assertStringContainsString('CSRF', $response['message']);
        }
    }
    
    public function testCsrfValidationFailsWithMissingTokenInSession() {
        $session = []; // no csrf_token
        $postData = [
            'identity' => 'test@example.com',
            'password' => 'password',
            'csrf_token' => 'provided_token'
        ];
        
        try {
            RequestHelper::postJson('Auth/login.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(403, $response['status']);
            $this->assertFalse($response['success']);
            $this->assertStringContainsString('CSRF', $response['message']);
        }
    }
}
