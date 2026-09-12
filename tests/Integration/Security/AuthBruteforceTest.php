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

    public function testUnlockWith60MinTemporaryPasswordRemovesUserFromLockedQueueAndEnforcesExpiry() {
        $db = Database::getInstance();
        $email = 'locked_remediation_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $customerId = UserFactory::createCustomer([
            'email' => $email,
            'password' => 'OriginalPassword123!'
        ]);
        $stmt = $db->prepare("SELECT id FROM users WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $userId = (int)$stmt->fetchColumn();

        // Lock the account with failed attempts and future locked_until
        $db->prepare("UPDATE users SET failed_attempts = 5, locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id = ?")
            ->execute([$userId]);

        $adminOpsService = new \App\Application\Services\AdminOperationsService(
            new \App\Infrastructure\Repositories\AdminOperationsRepository()
        );

        // 1. Verify user IS present in the active locked accounts list
        $lockedListBefore = $adminOpsService->getLockedAccounts(['tab' => 'locked', 'search' => $email, 'status' => '', 'type' => ''], 1, 10);
        $this->assertGreaterThanOrEqual(1, $lockedListBefore['total']);
        $this->assertEquals($userId, $lockedListBefore['accounts'][0]['id']);

        // 2. Admin unlocks the account and generates 60-minute temporary password
        $adminId = 1;
        $unlockRes = $adminOpsService->unlockAccount($userId, $adminId);
        $this->assertTrue($unlockRes['success']);
        $this->assertNotEmpty($unlockRes['temp_password']);
        $tempPassword = $unlockRes['temp_password'];

        // 3. Verify user is now REMOVED from the active locked accounts list
        $lockedListAfter = $adminOpsService->getLockedAccounts(['tab' => 'locked', 'search' => $email, 'status' => '', 'type' => ''], 1, 10);
        $this->assertEquals(0, $lockedListAfter['total'], 'Unlocked user must be removed from active locked accounts list');

        // 4. If filtering specifically by status = "done", user is visible within 60 minutes
        $doneList = $adminOpsService->getLockedAccounts(['tab' => 'locked', 'search' => $email, 'status' => 'done', 'type' => ''], 1, 10);
        $this->assertEquals(1, $doneList['total'], 'Unlocked user is visible under Done filter within 60 minutes');

        // 5. If simulated that 61 minutes have passed since temp password generation
        $db->prepare("UPDATE users SET updated_at = DATE_SUB(NOW(), INTERVAL 61 MINUTE) WHERE id = ?")
            ->execute([$userId]);

        // Under "done" filter, account is now excluded because 60 minutes have expired
        $doneListExpired = $adminOpsService->getLockedAccounts(['tab' => 'locked', 'search' => $email, 'status' => 'done', 'type' => ''], 1, 10);
        $this->assertEquals(0, $doneListExpired['total'], 'User must be removed from Done list after 60 minutes');

        // 6. Attempting to log in with the expired temp password fails with 401 expired message
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'identity' => $email,
            'password' => $tempPassword,
            'csrf_token' => 'dummy_token'
        ];
        try {
            RequestHelper::postJson('auth/login.php', $postData, $session);
            $this->fail("Expected temporary password expiration failure");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(401, $response['status']);
            $this->assertStringContainsString('expired (valid for 60 minutes)', $response['message']);
        }

        // 7. Verify a user logging in within the 60 minutes succeeds and clears temp_pass_status
        $emailValid = 'locked_valid_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $customerIdValid = UserFactory::createCustomer([
            'email' => $emailValid,
            'password' => 'SomePassword123!'
        ]);
        $stmt = $db->prepare("SELECT id FROM users WHERE customer_id = ?");
        $stmt->execute([$customerIdValid]);
        $userIdValid = (int)$stmt->fetchColumn();

        $unlockValid = $adminOpsService->unlockAccount($userIdValid, $adminId);
        $tempValidPass = $unlockValid['temp_password'];

        try {
            RequestHelper::postJson('auth/login.php', [
                'identity' => $emailValid,
                'password' => $tempValidPass,
                'csrf_token' => 'dummy_token'
            ], $session);
            $this->fail("Expected TestResponseException on successful login");
        } catch (\Throwable $e) {
            $loginResponse = json_decode($e->getMessage(), true);
            $this->assertEquals(200, $loginResponse['status'] ?? null, 'Expected 200 OK: ' . ($loginResponse['message'] ?? $e->getMessage()));
            $this->assertTrue($loginResponse['success'] ?? false, 'Login with temporary password within 60 minutes must succeed');
        }

        $stmt = $db->prepare("SELECT temp_pass_status, failed_attempts FROM users WHERE id = ?");
        $stmt->execute([$userIdValid]);
        $userStatus = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals('none', $userStatus['temp_pass_status']);
        $this->assertEquals(0, $userStatus['failed_attempts']);
    }
}
