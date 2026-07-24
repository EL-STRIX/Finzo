<?php
namespace Tests\Integration\Database;

use Tests\TestCase;
use Database\Migrator;
use App\Shared\Core\Database;

class MigrationTest extends TestCase {
    
    private Migrator $migrator;

    protected function setUp(): void {
        parent::setUp();
        
        $db = Database::getInstance();
        
        // Since TestCase wraps tests in transactions, 
        // DDL operations like CREATE TABLE might commit transactions implicitly in MySQL!
        // We need to commit the setUp transaction before running DDL, 
        // otherwise it will mess up the TestCase transaction management.
        if ($db->inTransaction()) {
            $db->commit();
        }
        
        require_once FINZO_ROOT . '/database/Migrator.php';
        
        $this->migrator = new \Database\Migrator(
            $db, 
            FINZO_ROOT . '/database/migrations', 
            FINZO_ROOT . '/database/seeds'
        );
    }

    protected function tearDown(): void {
        $db = Database::getInstance();
        
        // We must leave the database in a fully migrated and seeded state for the next tests
        $this->migrator->migrate();
        
        // We need to re-seed to make sure factory references exist
        $this->migrator->seed('system');
        
        // We do NOT call parent::tearDown() here because we've messed with the transactions 
        // explicitly in setUp() and we shouldn't attempt to rollback a committed transaction.
        // Wait, TestCase's tearDown checks if inTransaction() before rolling back, so it's safe to call it.
        parent::tearDown();
    }

    public function testMigrationResetAndMigrate() {
        // Suppress output
        ob_start();
        
        // 1. Reset database (rolls back all migrations)
        $this->migrator->reset();
        
        $db = Database::getInstance();
        
        // Verify tables are gone (check migrations table exists but users table doesn't)
        $stmt = $db->query("SHOW TABLES LIKE 'users'");
        $this->assertEquals(0, $stmt->rowCount());
        
        // 2. Run migrations
        $this->migrator->migrate();
        
        // Verify tables exist
        $stmt = $db->query("SHOW TABLES LIKE 'users'");
        $this->assertEquals(1, $stmt->rowCount());
        
        $output = ob_get_clean();
        
        $this->assertStringContainsString('Resetting database...', $output);
        $this->assertStringContainsString('Migrating:', $output);
        $this->assertStringContainsString('Database reset successfully', $output);
    }
}
