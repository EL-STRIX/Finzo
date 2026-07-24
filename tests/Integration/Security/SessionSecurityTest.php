<?php
namespace Tests\Integration\Security;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;

class SessionSecurityTest extends TestCase {
    
    public function testSessionInvalidationOnLogout() {
        $password = 'SecurePass123!';
        $email = 'session_test@example.com';
        
        $userId = UserFactory::createCustomer([
            'email' => $email,
            'password' => $password
        ]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'identity' => $email,
            'password' => $password,
            'csrf_token' => 'dummy_token'
        ];
        
        // 1. Perform Login
        try {
            RequestHelper::postJson('auth/login.php', $postData, $session);
        } catch (\Exception $e) {
            // Ignore response handling for now, we just want the session to be populated
        }
        
        // Ensure session is active
        $this->assertTrue(isset($_SESSION['user_id']));
        $this->assertEquals($userId, $_SESSION['user_customer_id']);
        
        // 2. Perform Logout
        $logoutData = ['csrf_token' => 'dummy_token'];
        try {
            RequestHelper::postJson('auth/logout.php', $logoutData, $_SESSION);
            $this->fail("json_response did not throw exception on logout");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(200, $response['status']);
        }
        
        // Ensure session is cleared/invalidated
        $this->assertFalse(isset($_SESSION['user_id']));
        $this->assertFalse(isset($_SESSION['role']));
        // session status might be PHP_SESSION_NONE or data is empty
    }
}
