<?php
namespace Tests\Integration\Database;

use Tests\TestCase;
use App\Shared\Core\Database;
use App\Infrastructure\Repositories\StaffRepository;

class StaffRepositoryTest extends TestCase {
    
    private StaffRepository $repository;
    
    protected function setUp(): void {
        parent::setUp();
        $this->repository = new StaffRepository(Database::getInstance());
    }

    public function testFindStaffById() {
        $db = Database::getInstance();
        $db->exec("
            INSERT INTO staff (full_name, personal_email, phone, staff_id, password_hash, status)
            VALUES ('Test Staff 2', 'staff.test2@example.com', '4444444444', 'STF888', 'hash', 'active')
        ");
        $id = $db->lastInsertId();
        
        $staff = $this->repository->findStaffById($id);
        
        $this->assertNotNull($staff);
        $this->assertEquals('Test Staff 2', $staff->full_name);
        $this->assertEquals('STF888', $staff->staff_id);
    }

    public function testUpdateStaffProfile() {
        $db = Database::getInstance();
        $db->exec("
            INSERT INTO staff (full_name, personal_email, phone, staff_id, password_hash, status)
            VALUES ('Old Staff', 'old.staff@example.com', '1111111111', 'STF111', 'hash', 'active')
        ");
        $id = $db->lastInsertId();
        
        $fields = ['full_name = ?', 'phone = ?'];
        $params = ['New Staff', '2222222222'];
        
        $success = $this->repository->updateStaffProfile($id, $fields, $params);
        $this->assertTrue($success);
        
        $staff = $this->repository->findStaffById($id);
        $this->assertEquals('New Staff', $staff->full_name);
        $this->assertEquals('2222222222', $staff->phone);
    }
}
