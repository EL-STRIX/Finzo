<?php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\AdminOperationsService;
use App\Infrastructure\Repositories\AdminOperationsRepository;
use PHPUnit\Framework\MockObject\MockObject;

class AdminOperationsServiceTest extends TestCase {
    protected AdminOperationsService $service;
    /** @var AdminOperationsRepository&MockObject */
    protected $repoMock;

    protected function setUp(): void {
        parent::setUp();
        
        $this->repoMock = $this->createMock(AdminOperationsRepository::class);
        $this->service = new AdminOperationsService($this->repoMock);
        
        if (!defined('FINZO_ROOT')) {
            define('FINZO_ROOT', dirname(dirname(dirname(__DIR__))));
        }
        require_once FINZO_ROOT . '/config/functions.php';
    }

    public function testGetAuditLogReturnsCorrectData() {
        $filters = [
            'action_type' => 'all',
            'actor_role'  => 'admin',
            'from_date'   => '',
            'to_date'     => '',
            'search'      => ''
        ];
        
        $this->repoMock->method('getAdminById')
            ->willReturn(['full_name' => 'Admin Name']);
            
        $this->repoMock->expects($this->once())
            ->method('countAuditLogs')
            ->willReturn(1);
            
        $this->repoMock->expects($this->once())
            ->method('getAuditLogs')
            ->willReturn([['id' => 1, 'action_type' => 'test_action', 'actor_id' => 'ADM999', 'entity_display_id' => 'USER1', 'actor_display_id' => 'ADM999', 'created_at' => '2023-01-01']]);
            
        $result = $this->service->getAuditLog($filters, 1, 10);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('entries', $result);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('ADM999', $result['entries'][0]['actor_id']);
        $this->assertArrayHasKey('stats', $result);
    }

    public function testExportAuditLogsReturnsGenerator() {
        $filters = ['search' => '', 'action_type' => 'all', 'actor_role' => 'all'];
        $generatorFunc = function() {
            yield ['Log ID' => 1, 'Action Type' => 'LOGIN'];
        };
        $this->repoMock->expects($this->once())
            ->method('exportAuditLogsGenerator')
            ->willReturn($generatorFunc());

        $generator = $this->service->exportAuditLogs($filters);
        $this->assertInstanceOf(\Generator::class, $generator);
        $rows = iterator_to_array($generator);
        $this->assertCount(1, $rows);
        $this->assertEquals('LOGIN', $rows[0]['Action Type']);
    }

    public function testGetLockedAccounts() {
        $filters = ['tab' => 'locked', 'search' => '', 'status' => 'all'];
        
        $this->repoMock->expects($this->once())
            ->method('countLockedAccounts')
            ->willReturn(5);
            
        $this->repoMock->expects($this->once())
            ->method('getLockedAccounts')
            ->willReturn([['id' => 1, 'email' => 'locked@example.com', 'updated_at' => '2023-01-01']]);
            
        $result = $this->service->getLockedAccounts($filters, 1, 10);
        
        $this->assertEquals(5, $result['total']);
        $this->assertCount(1, $result['accounts']);
    }

    public function testGetLockedAccountDetail() {
        $this->repoMock->expects($this->once())
            ->method('getLockedAccountDetail')
            ->with(1)
            ->willReturn([
                'id' => 1, 
                'email' => 'lockeddetail@example.com', 
                'locked_until' => '2099-01-01 00:00:00',
                'failed_attempts' => 5,
                'full_name' => 'John Doe',
                'opened_date' => '2023-01-01',
                'customer_id' => 'CUST123',
                'account_no' => 'ACC123',
                'balance' => 0.0
            ]);
            
        $result = $this->service->getLockedAccountDetail(1);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('lockeddetail@example.com', $result['user']['email']);
    }

    public function testRecoverUser() {
        $user = ['id' => 1, 'customer_id' => 'CUST123', 'is_deleted' => 1, 'full_name' => 'John Doe'];
        
        $this->repoMock->expects($this->once())
            ->method('getUserForRecovery')
            ->with(1)
            ->willReturn($user);
            
        $this->repoMock->expects($this->once())
            ->method('recoverUser')
            ->with(1, 'CUST123');
            
        $result = $this->service->recoverUser(1, 1);
        
        $this->assertTrue($result['success']);
    }

    public function testRecoverUserFailsIfNotDeleted() {
        $user = ['id' => 1, 'customer_id' => 'CUST123', 'is_deleted' => 0];
        
        $this->repoMock->expects($this->once())
            ->method('getUserForRecovery')
            ->with(1)
            ->willReturn($user);
            
        $result = $this->service->recoverUser(1, 1);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('User is not deleted', $result['message']);
    }

    public function testProcessApplicationApprove() {
        $user = ['id' => 1, 'customer_id' => 'CUST123', 'status' => 'pending', 'email' => 'test@test.com', 'full_name' => 'John Doe'];
        
        $this->repoMock->expects($this->once())
            ->method('getApplicationUser')
            ->with(1)
            ->willReturn($user);
            
        $this->repoMock->expects($this->once())
            ->method('getAccountPreferences')
            ->with('CUST123')
            ->willReturn(['account_type' => 'savings', 'initial_deposit' => 1000]);
            
        $this->repoMock->method('getAccountIdByCustomerId')
            ->willReturn(5);
            
        $this->repoMock->expects($this->once())
            ->method('approveApplication');

        $result = $this->service->processApplication(1, 'approve', '', '', 1);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('account_no', $result);
    }

    public function testProcessApplicationApproveFailsForActiveUser() {
        $user = ['id' => 1, 'customer_id' => 'CUST123', 'status' => 'active', 'email' => 'test@test.com', 'full_name' => 'John Doe'];

        $this->repoMock->expects($this->once())
            ->method('getApplicationUser')
            ->with(1)
            ->willReturn($user);

        // Approving an already-active customer must never touch the account again
        $this->repoMock->expects($this->never())
            ->method('approveApplication');

        $result = $this->service->processApplication(1, 'approve', '', '', 1);

        $this->assertFalse($result['success']);
        $this->assertEquals(409, $result['code']);
    }

    public function testProcessApplicationRejectFailsForProcessedUser() {
        $user = ['id' => 1, 'customer_id' => 'CUST123', 'status' => 'rejected', 'email' => 'test@test.com', 'full_name' => 'John Doe'];

        $this->repoMock->expects($this->once())
            ->method('getApplicationUser')
            ->with(1)
            ->willReturn($user);

        // Re-judging a finally rejected application must not be possible
        $this->repoMock->expects($this->never())
            ->method('rejectApplication');

        $result = $this->service->processApplication(1, 'reject', 'Documents invalid', '', 1);

        $this->assertFalse($result['success']);
        $this->assertEquals(409, $result['code']);
    }
}
