<?php
namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Shared\Core\Database;
use PDO;

abstract class TestCase extends BaseTestCase {
    /**
     * @var PDO
     */
    protected PDO $db;

    /**
     * Set up the test environment.
     * Executes before every single test.
     */
    protected function setUp(): void {
        parent::setUp();
        
        // Initialize the global database connection via the singleton wrapper
        $this->db = Database::getInstance();
        
        // Enforce strict test isolation by starting a database transaction.
        // This ensures no test logic permanently mutates the test database.
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
        
        // Purge all superglobals to prevent state leakage across tests
        $_POST = [];
        $_GET = [];
        $_SERVER = [];
        $_SESSION = [];
        
        // Re-inject baseline environment variables for predictable execution
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
    }

    /**
     * Tear down the test environment.
     * Executes after every single test.
     */
    protected function tearDown(): void {
        // Rollback the transaction to completely undo any database writes
        if (isset($this->db) && $this->db->inTransaction()) {
            $this->db->rollBack();
        }
        
        parent::tearDown();
    }

    /**
     * Helper: Authenticate as a specific user for the duration of the test.
     *
     * @param array $user Data representing the user
     * @param string $role The role (e.g., 'customer', 'admin', 'staff')
     * @return self
     */
    protected function actingAs(array $user, string $role = 'customer'): self {
        if (session_status() === PHP_SESSION_NONE) {
            \App\Shared\Security\SessionManager::start();
        }
        $_SESSION['user_id'] = $user['id'] ?? 1;
        $_SESSION['role'] = $role;
        $_SESSION['email'] = $user['email'] ?? 'test@finzo.com';
        
        // Additional mock context
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Test Runner';
        
        return $this;
    }
}
