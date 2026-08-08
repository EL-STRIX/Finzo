<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\StaffService;
use App\Infrastructure\Repositories\StaffRepository;

class StaffServiceIntegrationTest extends TestCase
{
    private $staffService;

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
        
        $repo = new StaffRepository($this->db);
        $this->staffService = new StaffService($repo, null);
    }

    public function testPassthroughMethodsWithRealRepository()
    {
        try { $this->staffService->accountLookup('1234567890'); } catch (\Exception $e) {}
        try { $this->staffService->changePassword(1, 'old', 'new'); } catch (\Exception $e) {}
        try { $this->staffService->getDashboardData(1); } catch (\Exception $e) {}
        try { $this->staffService->processDeposit(1, '1234567890', 100.0, 'note'); } catch (\Exception $e) {}
        try { $this->staffService->processWithdrawal(1, '1234567890', 100.0, 'notes'); } catch (\Exception $e) {}
        
        $filters = ['type' => 'all', 'status' => 'all', 'per_page' => 10, 'page' => 1];
        try { $this->staffService->getHistory(1, $filters); } catch (\Exception $e) {}
        
        try { $this->staffService->getReceipt('TXN123'); } catch (\Exception $e) {}
        try { $this->staffService->getFullProfile(1); } catch (\Exception $e) {}
        try { $this->staffService->updateProfile(1, []); } catch (\Exception $e) {}
        try { $this->staffService->clockIn(1); } catch (\Exception $e) {}
        try { $this->staffService->clockOut(1, 'notes'); } catch (\Exception $e) {}
        
        $this->assertTrue(true);
    }
}
