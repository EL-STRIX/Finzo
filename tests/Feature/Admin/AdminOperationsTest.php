<?php
namespace Tests\Feature\Admin;

use Tests\TestCase;
use Tests\Factories\UserFactory;
use Tests\Factories\AdminFactory;
use Tests\Helpers\RequestHelper;

class AdminOperationsTest extends TestCase {
    
    private int $adminId;
    private array $sessionData;
    
    protected function setUp(): void {
        parent::setUp();
        
        $this->adminId = AdminFactory::createAdmin([
            'email' => 'admin_' . rand(10000, 99999) . '@finzo.com',
            'admin_role' => 'admin'
        ]);
        
        $this->sessionData = [
            'admin_id' => $this->adminId,
            'role' => 'admin',
            'csrf_token' => 'dummy_token'
        ];

        // Ensure global functions are available
        if (!defined('FINZO_ROOT')) {
            define('FINZO_ROOT', dirname(dirname(dirname(__DIR__))));
        }
        require_once FINZO_ROOT . '/config/functions.php';
    }

    public function test_get_audit_log_endpoint() {
        $this->expectException(\Exception::class);
        
        try {
            RequestHelper::getJson('/Admin/audit-log.php', ['per_page' => 10], $this->sessionData);
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            if (!isset($response['success'])) {
                var_dump($e->getMessage());
            }
            $this->assertTrue($response['success']);
            $this->assertArrayHasKey('entries', $response['data']);
            $this->assertArrayHasKey('total', $response['data']);
            throw $e;
        }
    }

    public function test_get_locked_accounts_endpoint() {
        // Create a locked user
        $customerId = UserFactory::createCustomer(['email' => 'locked@test.com']);
        $this->db->prepare("UPDATE users SET failed_attempts = 3, temp_pass_status = 'pending' WHERE customer_id = ?")->execute([$customerId]);
        
        $this->expectException(\Exception::class);
        
        try {
            RequestHelper::getJson('/Admin/locked-accounts.php', ['tab' => 'locked'], $this->sessionData);
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertTrue($response['success']);
            
            $found = false;
            foreach ($response['data']['accounts'] as $acc) {
                if ($acc['email'] === 'locked@test.com') {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found);
            
            throw $e;
        }
    }

    public function test_recover_user_endpoint() {
        // Create soft deleted user
        $customerId = UserFactory::createCustomer(['email' => 'deleted@test.com']);
        $this->db->prepare("UPDATE users SET is_deleted = 1, status = 'suspended' WHERE customer_id = ?")->execute([$customerId]);
        $userId = $this->db->query("SELECT id FROM users WHERE customer_id = '$customerId'")->fetchColumn();
        
        $postData = [
            'user_id' => $userId,
            'csrf_token' => 'dummy_token'
        ];
        
        $this->expectException(\Exception::class);
        
        try {
            RequestHelper::postJson('/Admin/recover-user.php', $postData, $this->sessionData);
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertTrue($response['success']);
            $this->assertEquals('User recovered successfully', $response['message']);
            
            // Check DB
            $user = $this->db->query("SELECT is_deleted, status FROM users WHERE id = $userId")->fetch();
            $this->assertEquals(0, $user['is_deleted']);
            $this->assertEquals('active', $user['status']);
            
            throw $e;
        }
    }

    public function test_recover_user_fails_on_active_user() {
        $customerId = UserFactory::createCustomer(['email' => 'active@test.com']);
        $userId = $this->db->query("SELECT id FROM users WHERE customer_id = '$customerId'")->fetchColumn();
        
        $postData = [
            'user_id' => $userId,
            'csrf_token' => 'dummy_token'
        ];
        
        $this->expectException(\Exception::class);
        
        try {
            RequestHelper::postJson('/Admin/recover-user.php', $postData, $this->sessionData);
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertFalse($response['success']);
            $this->assertEquals('User is not deleted', $response['message']);
            
            throw $e;
        }
    }

    public function test_review_application_approve() {
        $customerId = UserFactory::createCustomer(['status' => 'pending']);
        $userId = $this->db->query("SELECT id FROM users WHERE customer_id = '$customerId'")->fetchColumn();
        
        $postData = [
            'user_id' => $userId,
            'action' => 'approve',
            'csrf_token' => 'dummy_token'
        ];
        
        $this->expectException(\Exception::class);
        
        try {
            RequestHelper::postJson('/Admin/review-application.php', $postData, $this->sessionData);
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertTrue($response['success']);
            $this->assertEquals('Application approved successfully', $response['message']);
            
            // Verify status changed
            $status = $this->db->query("SELECT status FROM users WHERE id = $userId")->fetchColumn();
            $this->assertEquals('active', $status);
            
            throw $e;
        }
    }

    public function test_review_application_reject() {
        $customerId = UserFactory::createCustomer(['status' => 'pending']);
        $userId = $this->db->query("SELECT id FROM users WHERE customer_id = '$customerId'")->fetchColumn();
        
        $postData = [
            'user_id' => $userId,
            'action' => 'reject',
            'reason' => 'Blurry photo',
            'csrf_token' => 'dummy_token'
        ];
        
        $this->expectException(\Exception::class);
        
        try {
            RequestHelper::postJson('/Admin/review-application.php', $postData, $this->sessionData);
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertTrue($response['success']);
            $this->assertEquals('Application rejected', $response['message']);
            
            $user = $this->db->query("SELECT status, rejection_reason FROM users WHERE id = $userId")->fetch();
            $this->assertEquals('rejected', $user['status']);
            $this->assertEquals('Blurry photo', $user['rejection_reason']);
            
            throw $e;
        }
    }
}
