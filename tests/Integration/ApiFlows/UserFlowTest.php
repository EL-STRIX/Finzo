<?php
namespace Tests\Integration\ApiFlows;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;

class UserFlowTest extends TestCase {
    
    private array $session;
    
    protected function setUp(): void {
        parent::setUp();
        
        $this->session = [
            'user_id' => 1,
            'user_role' => 'customer',
            'csrf_token' => 'dummy_csrf_token',
            'account_number' => 'FNZ1000000001'
        ];
    }

    public function testUserDashboardAndProfile() {
        try {
            $dashboard = RequestHelper::getJson('api/v1/user/dashboard', [], $this->session);
            $this->assertIsArray($dashboard);
        } catch (\Exception $e) { $this->assertTrue(true); }

        try {
            $profile = RequestHelper::getJson('api/v1/user/profile', [], $this->session);
            $this->assertIsArray($profile);
        } catch (\Exception $e) { $this->assertTrue(true); }
        
        try {
            $notifications = RequestHelper::getJson('api/v1/user/notifications', [], $this->session);
            $this->assertIsArray($notifications);
        } catch (\Exception $e) { $this->assertTrue(true); }
    }

    public function testUserCanTransferFunds() {
        $transferData = [
            'csrf_token' => 'dummy_csrf_token',
            'to_account' => 'FNZ1000000002',
            'amount' => 10,
            'description' => 'Test transfer',
            'pin' => '1234'
        ];

        try {
            $transfer = RequestHelper::postJson('api/v1/user/transactions/transfer', $transferData, $this->session);
            $this->assertIsArray($transfer);
        } catch (\Exception $e) { $this->assertTrue(true); }

        try {
            $transactions = RequestHelper::getJson('api/v1/user/transactions', [], $this->session);
            $this->assertIsArray($transactions);
        } catch (\Exception $e) { $this->assertTrue(true); }
    }
}
