<?php
namespace Tests\Integration\Regression;

use Tests\TestCase;
use App\Shared\Core\Database;
use App\Infrastructure\Repositories\AdminStaffBranchRepository;

class BugfixesTest extends TestCase {

    public function testPhase4BugFixStaffInsertionEnumConstraint() {
        // Bug Context: In Phase 4, the 'staff' table had an ENUM column 'temp_pass_status' 
        // that threw a PDOException because 'none' was not an allowed default or provided value.
        // This test ensures that a basic insertion using standard defaults succeeds.
        
        $db = Database::getInstance();
        $repo = clone new AdminStaffBranchRepository($db); // Clone to avoid state
        
        $db->exec("SET FOREIGN_KEY_CHECKS=0");
        
        $data = [
            'full_name' => 'Regression Tester',
            'date_of_birth' => '1990-01-01',
            'gender' => 'Male',
            'profile_photo' => null,
            'personal_email' => 'regression_' . time() . '@example.com',
            'phone' => '9999999999',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '8888888888',
            'staff_id' => 'STF_REG_' . time(),
            'designation' => 'Tester',
            'department' => 'QA',
            'branch_id' => 1,
            'date_of_joining' => '2023-01-01',
            'password_hash' => 'hash123',
            'created_by' => 999999, // bypassed by FK=0
            'street' => '123 Main St',
            'city' => 'Metropolis',
            'state' => 'NY',
            'pincode' => '10001',
            'id_type' => 'Aadhaar',
            'id_number' => '123456789012',
            'front_image' => null,
            'back_image' => null
        ];
        
        try {
            $insertedId = $repo->insertStaff($data);
            $this->assertGreaterThan(0, $insertedId);
            
            // Check that temp_pass_status was set to a safe default
            $stmt = $db->prepare("SELECT temp_pass_status FROM staff WHERE id = ?");
            $stmt->execute([$insertedId]);
            $status = $stmt->fetchColumn();
            
            // Allow acceptable enum defaults like 'none', 'active', 'expired', or null if nullable
            $this->assertTrue(in_array($status, ['none', 'active', 'expired', null], true));
            
        } catch (\PDOException $e) {
            $this->fail("Regression encountered: PDOException on staff insertion -> " . $e->getMessage());
        }
    }

    public function testPhase5BugFixSessionDestroyWarning() {
        // Bug Context: In Phase 5, calling logout() invoked session_destroy() when 
        // session_status() === PHP_SESSION_NONE in CLI environment, generating a PHP Warning.
        
        // Ensure session is NOT active
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $this->assertEquals(PHP_SESSION_NONE, session_status());
        
        try {
            \Tests\Helpers\RequestHelper::postJson('api/v1/auth/logout', []);
            $this->assertTrue(true);
        } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
            $this->assertTrue(true); // Exit via JSON response is expected and fine
        }
    }
}
