<?php
namespace Tests\Integration\Banking;

use Tests\TestCase;
use App\Shared\Core\Database;
use Tests\Factories\UserFactory;

class TransactionAtomicityTest extends TestCase {
    
    public function testTransactionRollbackOnPartialFailure() {
        $db = Database::getInstance();
        
        $senderId = UserFactory::createCustomer();
        $receiverId = UserFactory::createCustomer();
        
        $senderAccountNo = UserFactory::createAccount($senderId, ['balance' => 5000]);
        $receiverAccountNo = UserFactory::createAccount($receiverId, ['balance' => 1000]);
        
        $stmt = $db->prepare("SELECT id FROM accounts WHERE account_no = ?");
        $stmt->execute([$senderAccountNo]);
        $senderAccountId = $stmt->fetchColumn();
        
        $stmt->execute([$receiverAccountNo]);
        $receiverAccountId = $stmt->fetchColumn();
        
        // Commit the TestCase transaction so we can manually manage transactions in this test
        if ($db->inTransaction()) {
            $db->commit();
        }
        
        // We will manually perform the transaction steps and throw an exception to simulate failure
        try {
            $db->beginTransaction();
            
            // Step 1: Debit sender
            $db->exec("UPDATE accounts SET balance = balance - 1000 WHERE id = {$senderAccountId}");
            
            // Ensure debit happened inside the transaction
            $stmt = $db->query("SELECT balance FROM accounts WHERE id = {$senderAccountId}");
            $this->assertEquals(4000, $stmt->fetchColumn());
            
            // Step 2: Throw exception before Credit receiver
            throw new \Exception("Simulated Failure");
            
            $db->exec("UPDATE accounts SET balance = balance + 1000 WHERE id = {$receiverAccountId}");
            $db->commit();
            
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
        }
        
        // Restart transaction for tearDown
        $db->beginTransaction();
        
        // Assert: Sender balance must be reverted to 5000
        $stmt = $db->query("SELECT balance FROM accounts WHERE id = {$senderAccountId}");
        $this->assertEquals(5000, $stmt->fetchColumn());
        
        // Assert: Receiver balance must remain 1000
        $stmt = $db->query("SELECT balance FROM accounts WHERE id = {$receiverAccountId}");
        $this->assertEquals(1000, $stmt->fetchColumn());
    }
}
