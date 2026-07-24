<?php
namespace Tests\Integration\Database;

use Tests\TestCase;
use App\Shared\Core\Database;
use App\Infrastructure\Repositories\UserRepository;
use Tests\Factories\UserFactory;

class UserRepositoryTest extends TestCase {
    
    private UserRepository $repository;
    
    protected function setUp(): void {
        parent::setUp();
        $this->repository = new UserRepository(Database::getInstance());
    }

    public function testGetCustomerId() {
        $customerId = UserFactory::createCustomer(['full_name' => 'Repo User']);
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM users WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $userId = $stmt->fetchColumn();
        
        $result = $this->repository->getCustomerId($userId);
        
        $this->assertEquals($customerId, $result);
    }

    public function testUpdateProfile() {
        $customerId = UserFactory::createCustomer([
            'full_name' => 'Old Name',
            'phone' => '1234567890',
            'email' => 'old@example.com'
        ]);
        
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM users WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $userId = $stmt->fetchColumn();
        
        $success = $this->repository->updateProfile($userId, 'New Name', '9876543210', 'new@example.com');
        
        $this->assertTrue($success);
        
        $profile = $this->repository->getFullProfile($userId);
        
        $this->assertEquals('New Name', $profile['full_name']);
        $this->assertEquals('9876543210', $profile['phone']);
        $this->assertEquals('new@example.com', $profile['email']);
    }

    public function testBeneficiaryManagement() {
        $customerId = UserFactory::createCustomer();
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM users WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $userId = $stmt->fetchColumn();
        
        // Add Beneficiary
        $beneficiaryId = $this->repository->addBeneficiary($customerId, 'My Bro', 'FNZ0000001', 'Test Bank');
        $this->assertGreaterThan(0, $beneficiaryId);
        
        // Count Beneficiaries
        $count = $this->repository->countBeneficiaries($customerId);
        $this->assertEquals(1, $count);
        
        // Get Beneficiaries
        $list = $this->repository->fetchBeneficiaries($customerId);
        $this->assertCount(1, $list);
        $this->assertEquals('My Bro', $list[0]['nickname']);
        
        // Delete Beneficiary
        $deleted = $this->repository->deleteBeneficiary($beneficiaryId, $customerId);
        $this->assertEquals(1, $deleted);
        
        $countAfter = $this->repository->countBeneficiaries($customerId);
        $this->assertEquals(0, $countAfter);
    }
}
