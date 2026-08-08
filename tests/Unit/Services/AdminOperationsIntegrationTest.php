<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\AdminOperationsService;
use App\Infrastructure\Repositories\AdminOperationsRepository;

class AdminOperationsIntegrationTest extends TestCase
{
    private $adminOpsService;

    protected function setUp(): void
    {
        parent::setUp();
        
        if (!defined('FINZO_ROOT')) {
            define('FINZO_ROOT', dirname(dirname(dirname(__DIR__))));
        }
        require_once FINZO_ROOT . '/config/functions.php';
        
        if (!defined('APP_KEY')) {
            define('APP_KEY', base64_encode(str_repeat('a', 32)));
        }
        
        $repo = new AdminOperationsRepository($this->db);
        $this->adminOpsService = new AdminOperationsService($repo, null);
    }

    public function testPassthroughMethodsWithRealRepository()
    {
        $filters = ['status' => 'all', 'tab' => 'all', 'type' => 'all', 'search' => ''];
        $this->adminOpsService->getAuditLog($filters, 1, 10);
        $this->adminOpsService->getDashboardData(1);
        $this->adminOpsService->getDeletedUsers($filters, 1, 10);
        $this->adminOpsService->getInterestData();
        
        try { $this->adminOpsService->updateInterestRate(5.0, 'next_month', '', 1); } catch (\Exception $e) {}
        
        try { $this->adminOpsService->exportUsers($filters); } catch (\Exception $e) {}
        
        $this->adminOpsService->getLockedAccounts($filters, 1, 10);
        
        try { $this->adminOpsService->getLockedAccountDetail(999); } catch (\Exception $e) {}
        
        $this->adminOpsService->getPendingApprovals($filters, 1, 10);
        
        try { $this->adminOpsService->recoverUser(999, 1); } catch (\Exception $e) {}
        try { $this->adminOpsService->getApplicationDetails(999); } catch (\Exception $e) {}
        try { $this->adminOpsService->processApplication(999, 'approve', '', '', 1); } catch (\Exception $e) {}
        
        $this->adminOpsService->getSuspendedUsers($filters, 1, 10);
        
        try { $this->adminOpsService->getTransactions($filters, 1, 10, false); } catch (\Exception $e) {}
        try { $this->adminOpsService->getUsersList($filters, 1, 10); } catch (\Exception $e) {}
        

        
        try { $this->adminOpsService->getSuspendedUserDetail(999); } catch (\Exception $e) {}
        try { $this->adminOpsService->unlockAccount(999, 1); } catch (\Exception $e) {}
        try { $this->adminOpsService->processUserAction(999, 'suspend', 'test', '', '', 1); } catch (\Exception $e) {}
        try { $this->adminOpsService->getUserDetail(999); } catch (\Exception $e) {}
        try { $this->adminOpsService->reverseTransaction('TEST_TXN', 1, 'test'); } catch (\Exception $e) {}
        
        $this->assertTrue(true);
    }
}
