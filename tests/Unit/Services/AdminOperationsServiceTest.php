<?php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AdminOperationsService;
use App\Repositories\AdminOperationsRepository;
use Tests\Factories\UserFactory;
use Tests\Factories\AdminFactory;

class AdminOperationsServiceTest extends TestCase {
    protected AdminOperationsService $service;
    protected AdminOperationsRepository $repository;

    protected function setUp(): void {
        parent::setUp();
        $this->repository = new AdminOperationsRepository();
        $this->service = new AdminOperationsService($this->repository);
        
        // Ensure functions.php and mailer.php are loaded for the service to use global functions like write_audit_log
        if (!defined('FINZO_ROOT')) {
            define('FINZO_ROOT', dirname(dirname(dirname(__DIR__))));
        }
        require_once FINZO_ROOT . '/config/functions.php';
    }

    public function testGetAuditLogReturnsCorrectData() {
        // Create admin and generate some audit logs
        $adminId = AdminFactory::createAdmin(['admin_id' => 'ADM999', 'full_name' => 'Audit Admin']);
        
        // Insert dummy audit log
        $this->db->exec("INSERT INTO audit_log (action_type, entity_id, entity_type, actor_id, actor_role, details, ip_address, created_at)
                         VALUES ('test_action', '1', 'system', 'ADM999', 'admin', 'Test details', '127.0.0.1', NOW())");
        
        $filters = [
            'action_type' => 'all',
            'actor_role'  => 'admin',
            'from_date'   => '',
            'to_date'     => '',
            'search'      => ''
        ];
        
        $result = $this->service->getAuditLog($filters, 1, 10);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('entries', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertGreaterThan(0, $result['total']);
        
        // Check if the log we just inserted is present
        $found = false;
        foreach ($result['entries'] as $entry) {
            if ($entry['actor_id'] === 'ADM999' && $entry['action_type'] === 'test_action') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testGetLockedAccounts() {
        // Create a locked user
        $customerId = UserFactory::createCustomer([
            'email' => 'locked@example.com',
            'full_name' => 'Locked User'
        ]);
        
        // Lock the user manually
        $stmt = $this->db->prepare("UPDATE users SET failed_attempts = 3, temp_pass_status = 'pending', locked_until = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        
        $filters = [
            'tab'    => 'locked',
            'search' => 'Locked User',
            'status' => 'pending'
        ];
        
        $result = $this->service->getLockedAccounts($filters, 1, 10);
        
        $this->assertIsArray($result);
        $this->assertGreaterThan(0, $result['total']);
        
        $found = false;
        foreach ($result['accounts'] as $acc) {
            if ($acc['email'] === 'locked@example.com') {
                $found = true;
                $this->assertEquals('Pending', $acc['status_text']);
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testGetLockedAccountDetail() {
        $customerId = UserFactory::createCustomer([
            'email' => 'lockeddetail@example.com'
        ]);
        UserFactory::createAccount($customerId);
        
        $this->db->prepare("UPDATE users SET failed_attempts = 5, locked_until = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE customer_id = ?")->execute([$customerId]);
        
        // Get internal ID
        $userId = $this->db->query("SELECT id FROM users WHERE customer_id = '$customerId'")->fetchColumn();
        
        $result = $this->service->getLockedAccountDetail((int)$userId);
        
        $this->assertTrue($result['success']);
        $this->assertTrue($result['user']['is_locked']);
        $this->assertEquals('lockeddetail@example.com', $result['user']['email']);
    }

    public function testRecoverUser() {
        $customerId = UserFactory::createCustomer([
            'email' => 'deleted@example.com'
        ]);
        UserFactory::createAccount($customerId, ['status' => 'closed']);
        
        // Soft delete user
        $this->db->prepare("UPDATE users SET is_deleted = 1, status = 'suspended' WHERE customer_id = ?")->execute([$customerId]);
        $userId = $this->db->query("SELECT id FROM users WHERE customer_id = '$customerId'")->fetchColumn();
        
        $adminId = AdminFactory::createAdmin();
        
        $result = $this->service->recoverUser((int)$userId, $adminId);
        
        $this->assertTrue($result['success']);
        
        // Verify in DB
        $user = $this->db->query("SELECT is_deleted, status FROM users WHERE id = $userId")->fetch();
        $this->assertEquals(0, $user['is_deleted']);
        $this->assertEquals('active', $user['status']);
        
        $account = $this->db->query("SELECT status FROM accounts WHERE user_id = '$customerId'")->fetch();
        $this->assertEquals('active', $account['status']);
    }

    public function testRecoverUserFailsIfNotDeleted() {
        $customerId = UserFactory::createCustomer(['email' => 'active@example.com']);
        $userId = $this->db->query("SELECT id FROM users WHERE customer_id = '$customerId'")->fetchColumn();
        $adminId = AdminFactory::createAdmin();
        
        $result = $this->service->recoverUser((int)$userId, $adminId);
        
        $this->assertFalse($result['success']);
        $this->assertEquals(409, $result['code']);
        $this->assertEquals('User is not deleted', $result['message']);
    }

    public function testProcessApplicationApprove() {
        $customerId = UserFactory::createCustomer(['status' => 'pending']);
        $userId = $this->db->query("SELECT id FROM users WHERE customer_id = '$customerId'")->fetchColumn();
        $adminId = AdminFactory::createAdmin();

        // The service opens its own transaction internally.
        // Commit our outer test transaction so nested transactions don't conflict.
        $this->db->commit();

        $result = $this->service->processApplication((int)$userId, 'approve', '', '', $adminId);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('account_no', $result);

        // Check DB
        $user = $this->db->query("SELECT status FROM users WHERE id = $userId")->fetch();
        $this->assertEquals('active', $user['status']);

        // Clean up manually since we committed
        $this->db->exec("DELETE FROM users WHERE id = $userId");
        $this->db->beginTransaction(); // Restart for tearDown
    }

    public function testProcessApplicationReject() {
        $customerId = UserFactory::createCustomer(['status' => 'pending']);
        $userId = $this->db->query("SELECT id FROM users WHERE customer_id = '$customerId'")->fetchColumn();
        $adminId = AdminFactory::createAdmin();

        // Commit outer test transaction so service can open its own
        $this->db->commit();

        $result = $this->service->processApplication((int)$userId, 'reject', 'Missing documents', '', $adminId);

        $this->assertTrue($result['success']);

        // Check DB
        $user = $this->db->query("SELECT status, rejection_reason FROM users WHERE id = $userId")->fetch();
        $this->assertEquals('rejected', $user['status']);
        $this->assertEquals('Missing documents', $user['rejection_reason']);

        // Clean up manually
        $this->db->exec("DELETE FROM users WHERE id = $userId");
        $this->db->beginTransaction(); // Restart for tearDown
    }
}
