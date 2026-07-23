<?php
namespace Tests\Integration\Database;

use Tests\TestCase;
use Database\Migrator;
use App\Shared\Core\Database;

class SeederTest extends TestCase {
    
    private Migrator $migrator;

    protected function setUp(): void {
        parent::setUp();
        $db = Database::getInstance();
        require_once FINZO_ROOT . '/database/Migrator.php';
        
        $this->migrator = new \Database\Migrator(
            $db, 
            FINZO_ROOT . '/database/migrations', 
            FINZO_ROOT . '/database/seeds'
        );
    }

    public function testSystemSeederIsIdempotent() {
        $db = Database::getInstance();
        
        // Ensure first seed
        ob_start();
        $this->migrator->seed('system');
        ob_get_clean();
        
        // Count before
        $stmt = $db->query("SELECT COUNT(*) FROM admin_users WHERE email = 'admin@finzo.com'");
        $initialCount = $stmt->fetchColumn();
        
        // Ensure it's at least 1 (since TestCase or MigrationTest might have already seeded it)
        $this->assertGreaterThanOrEqual(1, $initialCount);
        
        // Run seeder again
        ob_start();
        $this->migrator->seed('system');
        $output = ob_get_clean();
        
        $this->assertStringContainsString('System seeds injected successfully.', $output);
        
        // Count after - should remain exactly the same due to INSERT IGNORE
        $stmt = $db->query("SELECT COUNT(*) FROM admin_users WHERE email = 'admin@finzo.com'");
        $finalCount = $stmt->fetchColumn();
        
        $this->assertEquals($initialCount, $finalCount);
        $this->assertEquals(1, $finalCount, "Should not create duplicate admins");
    }
}
