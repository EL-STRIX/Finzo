<?php
namespace Tests\Integration\Banking;

use Tests\TestCase;
use App\Shared\Core\Database;
use Tests\Factories\UserFactory;

class FinancialPrecisionTest extends TestCase {
    
    public function testFloatingPointPrecision() {
        $db = Database::getInstance();
        
        $customerId = UserFactory::createCustomer();
        // Insert account with 1000.01 balance (must be > 500 min deposit)
        $accountNo = UserFactory::createAccount($customerId, ['balance' => 1000.01]);
        
        // Let's add 0.02 and subtract 0.03
        // If it was float, 100.01 + 0.02 - 0.03 might result in 100.00000000000001
        $db->exec("UPDATE accounts SET balance = balance + 0.02 WHERE account_no = '{$accountNo}'");
        $db->exec("UPDATE accounts SET balance = balance - 0.03 WHERE account_no = '{$accountNo}'");
        
        $stmt = $db->prepare("SELECT balance FROM accounts WHERE account_no = ?");
        $stmt->execute([$accountNo]);
        $balance = $stmt->fetchColumn();
        
        // Assert precision is exact 1000.00
        $this->assertEquals('1000.00', $balance);
    }
    
    public function testLargeTransactionValues() {
        $db = Database::getInstance();
        
        $customerId = UserFactory::createCustomer();
        // 999,999,999,999.99
        $largeAmount = 999999999999.99;
        
        $accountNo = UserFactory::createAccount($customerId, ['balance' => $largeAmount]);
        
        $stmt = $db->prepare("SELECT balance FROM accounts WHERE account_no = ?");
        $stmt->execute([$accountNo]);
        $balance = $stmt->fetchColumn();
        
        $this->assertEquals('999999999999.99', $balance);
    }
}
