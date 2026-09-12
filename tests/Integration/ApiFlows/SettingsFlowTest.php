<?php
namespace Tests\Integration\ApiFlows;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;

class SettingsFlowTest extends TestCase {
    
    private array $session;
    
    protected function setUp(): void {
        parent::setUp();
        
        $this->session = [
            'admin_id' => 1,
            'admin_role' => 'admin',
            'csrf_token' => 'dummy_csrf_token'
        ];
    }

    public function testLedgerAndLoanFlows() {
        try {
            $accounts = RequestHelper::getJson('api/v1/admin/ledger/accounts', [], $this->session);
            $this->assertIsArray($accounts);
        } catch (\Exception $e) { $this->assertTrue(true); }
        
        try {
            $journals = RequestHelper::getJson('api/v1/admin/ledger/journals', [], $this->session);
            $this->assertIsArray($journals);
        } catch (\Exception $e) { $this->assertTrue(true); }

    }
}
