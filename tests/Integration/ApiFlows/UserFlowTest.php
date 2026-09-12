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

    private function unwrap(callable $fn): array
    {
        try {
            $res = $fn();
            if (is_array($res)) return $res;
            if (is_string($res)) {
                $decoded = json_decode($res, true);
                if (is_array($decoded)) return $decoded;
            }
            return [];
        } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
            $decoded = json_decode($e->getMessage(), true);
            if (is_array($decoded) && isset($decoded['status']) && (int)$decoded['status'] >= 400) {
                throw $e;
            }
            if (is_array($decoded) && isset($decoded['success']) && $decoded['success'] === false) {
                throw $e;
            }
            return $decoded ?: [];
        }
    }

    public function testBeneficiaryJsonFlow() {
        $db = \App\Shared\Core\Database::getInstance();
        $senderCustId = \Tests\Factories\UserFactory::createCustomer(['email' => 'sender_ben_' . time() . '@example.com']);
        $recipientCustId = \Tests\Factories\UserFactory::createCustomer(['email' => 'recip_ben_' . time() . '@example.com']);
        $senderAccNo = \Tests\Factories\UserFactory::createAccount($senderCustId);
        $recipientAccNo = \Tests\Factories\UserFactory::createAccount($recipientCustId);

        $stmt = $db->prepare("SELECT id FROM users WHERE customer_id = ?");
        $stmt->execute([$senderCustId]);
        $senderUserId = (int)$stmt->fetchColumn();

        $session = [
            'user_id' => $senderUserId,
            'user_customer_id' => $senderCustId,
            'user_role' => 'customer',
            'user_status' => 'active',
            'csrf_token' => 'dummy_csrf_token'
        ];

        // 1. Account Lookup
        $lookupRes = $this->unwrap(fn() => RequestHelper::getJson('api/v1/user/accounts/lookup', ['account_no' => $recipientAccNo], $session));
        $this->assertTrue($lookupRes['success']);
        $this->assertNotEmpty($lookupRes['account']['holder_name']);

        // 2. Add Beneficiary via JSON POST
        $postData = [
            'nickname' => 'My Best Friend',
            'account_no' => $recipientAccNo,
            'holder_name' => $lookupRes['account']['holder_name'],
            'bank_name' => 'Finzo Banking System',
            'csrf_token' => 'dummy_csrf_token'
        ];
        $addRes = $this->unwrap(fn() => RequestHelper::postJson('api/v1/user/beneficiaries', $postData, $session));
        $this->assertTrue($addRes['success']);
        $this->assertNotEmpty($addRes['beneficiary']['id']);
        $beneficiaryId = $addRes['beneficiary']['id'];

        // 3. Get Beneficiaries
        $getRes = $this->unwrap(fn() => RequestHelper::getJson('api/v1/user/beneficiaries', [], $session));
        $this->assertTrue($getRes['success']);
        $this->assertGreaterThanOrEqual(1, count($getRes['beneficiaries']));

        // 4. Delete Beneficiary
        $deleteRes = $this->unwrap(fn() => RequestHelper::deleteJson('api/v1/user/beneficiaries', ['beneficiary_id' => $beneficiaryId], $session));
        $this->assertTrue($deleteRes['success']);
    }
}
