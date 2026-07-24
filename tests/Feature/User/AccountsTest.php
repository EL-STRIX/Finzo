<?php
namespace Tests\Feature\User;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;
use App\Shared\Core\Database;

class AccountsTest extends TestCase {
    
    private array $session;
    private string $accountNo;
    private int $userId;
    private string $customerId;

    protected function setUp(): void {
        parent::setUp();
        
        $this->customerId = UserFactory::createCustomer();
        $this->accountNo = UserFactory::createAccount($this->customerId);
        
        $db = Database::getInstance();
        $user = $db->query("SELECT id FROM users WHERE customer_id = '{$this->customerId}'")->fetch();
        $this->userId = (int) $user['id'];
        
        $this->session = [
            'user_id' => $this->userId,
            'user_customer_id' => $this->customerId,
            'role' => 'customer',
            'csrf_token' => 'dummy_token'
        ];
    }

    public function testGetAccounts() {
        try {
            RequestHelper::getJson('User/accounts.php', [], $this->session);
            $this->fail("Expected TestResponseException to be thrown");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertTrue($response['success']);
            $this->assertEquals(200, $response['status']);
            $this->assertArrayHasKey('account', $response['data']);
            $this->assertEquals($this->accountNo, $response['data']['account']['account_no']);
        }
    }

    public function testAccountLookupSuccess() {
        try {
            RequestHelper::getJson('User/account-lookup.php', ['account_no' => $this->accountNo], $this->session);
            $this->fail("Expected TestResponseException to be thrown");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertTrue($response['success']);
            $this->assertEquals(200, $response['status']);
            $this->assertArrayHasKey('holder_name', $response['data']['account']);
            $this->assertEquals('savings', $response['data']['account']['account_type']);
        }
    }

    public function testAccountLookupNotFound() {
        try {
            RequestHelper::getJson('User/account-lookup.php', ['account_no' => 'NONEXISTENT999'], $this->session);
            $this->fail("Expected TestResponseException to be thrown");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertFalse($response['success']);
            // It might be 404 or 422 depending on implementation
            $this->assertContains($response['status'], [404, 422, 400]);
        }
    }

    public function testAccountSettingsUpdate() {
        $postData = [
            'account_id' => $this->accountNo,
            'daily_limit' => 50000,
            'status' => 'active',
            'csrf_token' => 'dummy_token'
        ];

        try {
            RequestHelper::postJson('User/account-settings.php', $postData, $this->session);
            $this->fail("Expected TestResponseException to be thrown");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertTrue($response['success']);
            $this->assertEquals(200, $response['status']);
            
            $db = Database::getInstance();
            $account = $db->query("SELECT daily_limit FROM accounts WHERE account_no = '{$this->accountNo}'")->fetch();
            $this->assertEquals(50000.00, $account['daily_limit']);
        }
    }

    public function testAccountSettingsUpdateValidation() {
        $postData = [
            'account_id' => $this->accountNo,
            'daily_limit' => 500000, // Invalid, max is 100,000
            'status' => 'active',
            'csrf_token' => 'dummy_token'
        ];

        try {
            RequestHelper::postJson('User/account-settings.php', $postData, $this->session);
            $this->fail("Expected TestResponseException to be thrown");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertFalse($response['success']);
            $this->assertEquals(422, $response['status']);
            $this->assertEquals('Invalid limit value. Must be between 1,000 and 1,00,000', $response['message']);
        }
    }
}
