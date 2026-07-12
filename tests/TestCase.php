<?php
namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Core\Database;
use PDO;

abstract class TestCase extends BaseTestCase {
    protected PDO $db;

    protected function setUp(): void {
        parent::setUp();
        
        // Initialize the global connection using our wrapper
        $this->db = Database::getInstance();
        
        // Begin a database transaction before every test to ensure state is isolated
        // and doesn't pollute the test database.
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
        
        // Reset any superglobals that might have been polluted by previous tests
        $_POST = [];
        $_GET = [];
        $_SERVER = [];
        $_SESSION = [];
    }

    protected function tearDown(): void {
        // Rollback the transaction to undo all inserts/updates done during the test
        if (isset($this->db) && $this->db->inTransaction()) {
            $this->db->rollBack();
        }
        
        parent::tearDown();
    }
}
