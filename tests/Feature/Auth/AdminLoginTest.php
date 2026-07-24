<?php
namespace Tests\Feature\Auth;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;

class AdminLoginTest extends TestCase {
    
    public function testAdminLoginSuccess() {
        $password = 'Secret123!';
        $adminId = UserFactory::createAdmin([
            'email' => 'admin_login@example.com',
            'password' => $password
        ]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'email' => 'admin_login@example.com',
            'password' => $password,
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('auth/admin-login.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(200, $response['status']);
            $this->assertTrue($response['success']);
            
            // Check session
            $this->assertTrue(isset($_SESSION['admin_id']));
            $this->assertEquals('admin', $_SESSION['role']);
        }
    }

    public function testAdminLoginFailureWithInvalidPassword() {
        UserFactory::createAdmin([
            'email' => 'admin_wrong@example.com',
            'password' => 'Correct123!'
        ]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'email' => 'admin_wrong@example.com',
            'password' => 'Wrong123!',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('auth/admin-login.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(401, $response['status']);
            $this->assertFalse($response['success']);
        }
    }
}
