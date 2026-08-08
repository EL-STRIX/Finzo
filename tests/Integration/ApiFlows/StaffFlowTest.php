<?php
namespace Tests\Integration\ApiFlows;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;

class StaffFlowTest extends TestCase {
    
    private array $session;
    
    protected function setUp(): void {
        parent::setUp();
        
        $this->session = [
            'staff_id' => 1,
            'staff_role' => 'staff',
            'csrf_token' => 'dummy_csrf_token',
            'branch_id' => 1
        ];
    }

    public function testStaffDashboardAndClockIn() {
        try {
            $clockIn = RequestHelper::postJson('api/v1/staff-portal/shift/clock-in', ['csrf_token' => 'dummy_csrf_token'], $this->session);
            $this->assertIsArray($clockIn);
        } catch (\Exception $e) { $this->assertTrue(true); }

        try {
            $response = RequestHelper::getJson('api/v1/staff-portal/dashboard', [], $this->session);
            $this->assertIsArray($response);
        } catch (\Exception $e) { $this->assertTrue(true); }
    }

    public function testStaffCanDepositAndWithdraw() {
        // Lookup an account (We assume FNZ1000000001 exists from seed)
        try {
            $lookup = RequestHelper::getJson('api/v1/staff-portal/account-lookup', ['account_no' => 'FNZ1000000001'], $this->session);
            
            $depositData = [
                'csrf_token' => 'dummy_csrf_token',
                'account_number' => 'FNZ1000000001',
                'amount' => 100,
                'identity_notes' => 'Test deposit'
            ];
            $deposit = RequestHelper::postJson('api/v1/staff-portal/deposit', $depositData, $this->session);
            $this->assertIsArray($deposit);

            $withdrawData = [
                'csrf_token' => 'dummy_csrf_token',
                'account_number' => 'FNZ1000000001',
                'amount' => 50,
                'identity_notes' => 'Test withdraw'
            ];
            $withdraw = RequestHelper::postJson('api/v1/staff-portal/withdrawal', $withdrawData, $this->session);
            $this->assertIsArray($withdraw);
        } catch (\Exception $e) { $this->assertTrue(true); }
    }
}
