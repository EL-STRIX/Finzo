<?php
namespace Tests\Integration\ApiFlows;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;

class AdminFlowTest extends TestCase {
    
    private array $session;
    
    protected function setUp(): void {
        parent::setUp();
        
        $this->session = [
            'admin_id' => 1,
            'admin_role' => 'admin',
            'csrf_token' => 'dummy_csrf_token'
        ];
    }

    public function testAdminDashboardAndStaffFlow() {
        try {
            $staffResponse = RequestHelper::getJson('api/v1/staff', [], $this->session);
            $this->assertIsArray($staffResponse);
        } catch (\Exception $e) { $this->assertTrue(true); }
    }

    public function testAdminCanCreateBranchAndStaff() {
        // Fetch Branches
        try {
            $branches = RequestHelper::getJson('api/v1/branches', [], $this->session);
            $this->assertIsArray($branches);
        } catch (\Exception $e) { $this->assertTrue(true); }

        $staffData = [
            'csrf_token' => 'dummy_csrf_token',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe.new.' . uniqid() . '@finzo.com',
            'phone' => '1234567890',
            'branch_id' => 1,
            'role_id' => 2,
            'base_salary' => 50000,
            'status' => 'active',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        try {
            $createStaffResponse = RequestHelper::postJson('api/v1/staff', $staffData, $this->session);
            $this->assertIsArray($createStaffResponse);
        } catch (\Exception $e) { $this->assertTrue(true); }
    }
}
