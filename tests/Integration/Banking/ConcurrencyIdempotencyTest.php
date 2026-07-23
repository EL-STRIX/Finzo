<?php
namespace Tests\Integration\Banking;

use Tests\TestCase;
use App\Shared\Core\Database;
use Tests\Factories\UserFactory;

class ConcurrencyIdempotencyTest extends TestCase {
    
    public function testDatabaseLevelPessimisticLocking() {
        $db = Database::getInstance();
        
        $customerId = UserFactory::createCustomer();
        $accountNo = UserFactory::createAccount($customerId, ['balance' => 5000]);
        
        if ($db->inTransaction()) {
            $db->commit();
        }
        
        $db->beginTransaction();
        
        $stmt = $db->prepare("SELECT balance FROM accounts WHERE account_no = ? FOR UPDATE");
        $stmt->execute([$accountNo]);
        $lockedBalance = $stmt->fetchColumn();
        
        $this->assertEquals(5000, $lockedBalance);
        
        // Update balance
        $db->exec("UPDATE accounts SET balance = balance - 1000 WHERE account_no = '{$accountNo}'");
        $db->commit();
        
        $db->beginTransaction();
        
        // Verify final balance
        $stmt = $db->prepare("SELECT balance FROM accounts WHERE account_no = ?");
        $stmt->execute([$accountNo]);
        $this->assertEquals(4000, $stmt->fetchColumn());
    }

    public function testUniqueTransactionIdPreventsDuplicates() {
        $db = Database::getInstance();
        
        $senderId = UserFactory::createCustomer();
        $receiverId = UserFactory::createCustomer();
        
        $senderAccountId = $db->query("SELECT id FROM accounts WHERE account_no = '" . UserFactory::createAccount($senderId) . "'")->fetchColumn();
        $receiverAccountId = $db->query("SELECT id FROM accounts WHERE account_no = '" . UserFactory::createAccount($receiverId) . "'")->fetchColumn();
        
        $txnId = 'TXN' . bin2hex(random_bytes(8));
        
        // Insert first transaction
        $stmt = $db->prepare("
            INSERT INTO transactions (txn_id, from_account_id, to_account_id, type, amount, status)
            VALUES (?, ?, ?, 'transfer', 1000, 'completed')
        ");
        
        $success = $stmt->execute([$txnId, $senderAccountId, $receiverAccountId]);
        $this->assertTrue($success);
        
        // Try inserting exact same transaction ID (Idempotency simulation)
        $this->expectException(\PDOException::class);
        $this->expectExceptionCode('23000'); // Integrity constraint violation (Duplicate entry)
        
        $stmt->execute([$txnId, $senderAccountId, $receiverAccountId]);
    }
}
