<?php
namespace Tests\Integration\Banking;

use Tests\TestCase;
use App\Shared\Core\Database;
use App\Infrastructure\Repositories\UserRepository;
use Tests\Factories\UserFactory;

class AuditTrailTest extends TestCase {
    
    public function testAuditLogCanBeInsertedAndRetrieved() {
        $db = Database::getInstance();
        
        $customerId = UserFactory::createCustomer();
        
        // Simulating KYC Rejection Audit Log
        $db->exec("
            INSERT INTO audit_log (event_id, action_type, entity_type, entity_id, actor_id, actor_role, details)
            VALUES (CONCAT('EVT', UUID()), 'kyc_rejected', 'user', '{$customerId}', 'ADM001', 'admin', 'Signature mismatch')
        ");
        
        // Verify it was logged
        $stmt = $db->query("SELECT * FROM audit_log WHERE entity_id = '{$customerId}' AND action_type = 'kyc_rejected'");
        $log = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $this->assertNotEmpty($log);
        $this->assertEquals('Signature mismatch', $log['details']);
        $this->assertEquals('ADM001', $log['actor_id']);
        
        // Verify via Repository
        $repo = new UserRepository($db);
        $rejections = $repo->getPastRejections($customerId);
        
        $this->assertCount(1, $rejections);
        $this->assertEquals('Signature mismatch', $rejections[0]['details']);
    }
}
