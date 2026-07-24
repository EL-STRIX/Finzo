<?php
namespace Tests\Integration\Security;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;
use App\Shared\Core\Database;

class AuthBruteforceTest extends TestCase {
    
    public function testAccountLockoutAfterFailedAttempts() {
        $db = Database::getInstance();
        $email = 'bruteforce@example.com';
        
        $userId = UserFactory::createCustomer([
            'email' => $email,
            'password' => 'CorrectPassword123!'
        ]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'identity' => $email,
            'password' => 'WrongPassword123!',
            'csrf_token' => 'dummy_token'
        ];
        
        // Ensure max_failed_login_attempts is set to 5 for tests (or rely on default 5)
        $db->exec("INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_category, label) VALUES ('max_failed_login_attempts', '5', 'security', 'Max Failed Logins') ON DUPLICATE KEY UPDATE setting_value = '5'");
        
        // Perform 4 failed attempts (401 Unauthorized)
        for ($i = 1; $i <= 4; $i++) {
            try {
                RequestHelper::postJson('auth/login.php', $postData, $session);
                $this->fail("json_response did not throw exception on attempt $i");
            } catch (\Exception $e) {
                $response = json_decode($e->getMessage(), true);
                $this->assertEquals(401, $response['status']);
                $this->assertStringContainsString('remaining', $response['message']);
            }
        }
        
        // Perform the 5th failed attempt (should lock the account, 423 Locked)
        try {
            RequestHelper::postJson('auth/login.php', $postData, $session);
            $this->fail("json_response did not throw exception on 5th attempt");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(423, $response['status']);
            $this->assertStringContainsString('Account locked', $response['message']);
        }
        
        // Perform a 6th attempt with CORRECT password - should still be locked out!
        $postData['password'] = 'CorrectPassword123!';
        try {
            RequestHelper::postJson('auth/login.php', $postData, $session);
            $this->fail("json_response did not throw exception on 6th attempt (locked)");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(423, $response['status']);
            $this->assertStringContainsString('Account locked', $response['message']);
        }
    }
}
