<?php
namespace Tests\Feature\User;

use Tests\TestCase;
use Tests\Factories\UserFactory;
use Tests\Helpers\RequestHelper;
use App\Core\Database;
use PDO;

class TransferTest extends TestCase {
    
    private string $sourceCustomerId;
    private string $destCustomerId;
    private string $sourceAccountNo;
    private string $destAccountNo;
    
    protected function setUp(): void {
        parent::setUp();
        
        $this->sourceCustomerId = UserFactory::createCustomer(['email' => 'sender@test.com']);
        $this->sourceAccountNo = UserFactory::createAccount($this->sourceCustomerId, ['balance' => 10000.00]);
        
        // We need the internal user ID for session
        $stmt = $this->db->prepare("SELECT id FROM users WHERE customer_id = ?");
        $stmt->execute([$this->sourceCustomerId]);
        $this->sourceUserId = $stmt->fetchColumn();
        
        $this->destCustomerId = UserFactory::createCustomer(['email' => 'receiver@test.com']);
        $this->destAccountNo = UserFactory::createAccount($this->destCustomerId, ['balance' => 5000.00]);
        
        // Ensure the transaction PIN is set for the sender (default in factory is 1234 but wait, 
        // UserFactory doesn't set txn_pin by default, let's update the DB directly for the test)
        $this->db->exec("UPDATE users SET txn_pin = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE id = " . $this->sourceUserId);
        // The above hash is for '1234'
    }

    public function test_successful_transfer() {
        // Arrange
        $postData = [
            'to_account_no' => $this->destAccountNo,
            'amount' => '1500.00',
            'description' => 'Test transfer',
            'txn_pin' => '1234',
            'csrf_token' => 'dummy_token'
        ];
        
        $sessionData = [
            'user_id' => $this->sourceUserId,
            'user_status' => 'active',
            'csrf_token' => 'dummy_token' // Match the post token
        ];

        // Act & Assert
        $this->expectException(\Exception::class); // json_response throws exception in testing mode
        
        try {
            RequestHelper::postJson('/User/transfer.php', $postData, $sessionData);
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            
            $this->assertEquals(200, $response['status']);
            $this->assertTrue($response['data']['success']);
            $this->assertEquals(8500.00, $response['data']['new_balance']);
            
            // Verify database state
            $stmt = $this->db->prepare("SELECT balance FROM accounts WHERE account_no = ?");
            $stmt->execute([$this->destAccountNo]);
            $this->assertEquals(6500.00, $stmt->fetchColumn());
            
            // Re-throw to satisfy expectException if we want, but wait, catching it defeats expectException.
            // Let's just throw it again.
            throw $e;
        }
    }
    
    public function test_transfer_insufficient_funds() {
        // Arrange
        $postData = [
            'to_account_no' => $this->destAccountNo,
            'amount' => '50000.00',
            'description' => 'Too much',
            'txn_pin' => '1234',
            'csrf_token' => 'dummy_token'
        ];
        
        $sessionData = [
            'user_id' => $this->sourceUserId,
            'user_status' => 'active',
            'csrf_token' => 'dummy_token'
        ];

        // Act & Assert
        $this->expectException(\Exception::class);
        
        try {
            RequestHelper::postJson('/User/transfer.php', $postData, $sessionData);
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            
            $this->assertEquals(422, $response['status']);
            $this->assertFalse($response['data']['success']);
            $this->assertEquals('Insufficient balance', $response['data']['message']);
            
            throw $e;
        }
    }
}
