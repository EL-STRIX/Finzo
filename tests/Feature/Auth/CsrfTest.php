<?php
namespace Tests\Feature\Auth;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;

class CsrfTest extends TestCase {
    
    public function testGetCsrfToken() {
        try {
            RequestHelper::postJson('Auth/get-csrf-token.php', []);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(200, $response['status']);
            $this->assertTrue($response['data']['success']);
            $this->assertNotEmpty($response['data']['csrf_token']);
            
            // Check session
            $this->assertEquals($response['data']['csrf_token'], $_SESSION['csrf_token']);
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
            $this->assertFalse($response['data']['success']);
            $this->assertStringContainsString('CSRF', $response['data']['message']);
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
            $this->assertFalse($response['data']['success']);
            $this->assertStringContainsString('CSRF', $response['data']['message']);
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
            $this->assertFalse($response['data']['success']);
            $this->assertStringContainsString('CSRF', $response['data']['message']);
        }
    }
}
