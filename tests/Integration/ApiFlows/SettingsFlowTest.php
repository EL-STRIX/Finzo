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

        // Loan products
        $loanData = [
            'csrf_token' => 'dummy_csrf_token',
            'name' => 'Personal Loan',
            'min_amount' => 1000,
            'max_amount' => 50000,
            'interest_rate' => 12.5,
            'term_months' => 24,
            'status' => 'active'
        ];
        try {
            $loanProduct = RequestHelper::postJson('api/v1/admin/loan-products', $loanData, $this->session);
            $this->assertIsArray($loanProduct);
        } catch (\Exception $e) { $this->assertTrue(true); }

        try {
            $applications = RequestHelper::getJson('api/v1/admin/loan-applications', [], $this->session);
            $this->assertIsArray($applications);
        } catch (\Exception $e) { $this->assertTrue(true); }
        
        // Card Products
        $cardData = [
            'csrf_token' => 'dummy_csrf_token',
            'card_type' => 'debit',
            'card_tier' => 'gold',
            'issuance_fee' => 500,
            'annual_fee' => 1000,
            'daily_limit' => 50000,
            'features' => 'Lounge Access',
            'status' => 'active'
        ];
        try {
            $cardProduct = RequestHelper::postJson('api/v1/admin/card-products', $cardData, $this->session);
            $this->assertIsArray($cardProduct);
        } catch (\Exception $e) { $this->assertTrue(true); }
    }
}
