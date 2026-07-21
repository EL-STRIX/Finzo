<?php
namespace Tests\Feature\User;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;

class KycTest extends TestCase {
    protected string $dummyPng;
    
    protected function setUp(): void {
        parent::setUp();
        
        $this->dummyPng = tempnam(sys_get_temp_dir(), 'png');
        file_put_contents($this->dummyPng, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAACklEQVR4nGMAAQAABQABDQottAAAAABJRU5ErkJggg=='));
    }

    protected function tearDown(): void {
        if (file_exists($this->dummyPng)) unlink($this->dummyPng);
        
        // Remove dummy uploads created by the test
        $files = glob(dirname(__DIR__, 3) . '/public/uploads/profiles/*');
        foreach($files as $file){
            if(is_file($file)) unlink($file);
        }
        $files = glob(dirname(__DIR__, 3) . '/public/uploads/id-documents/*');
        foreach($files as $file){
            if(is_file($file)) unlink($file);
        }
        
        parent::tearDown();
    }

    private function getValidData(): array {
        return [
            'dob' => '2000-01-01',
            'gender' => 'Male',
            'father_name' => 'John Doe Sr',
            'mother_name' => 'Jane Doe Sr',
            'marital_status' => 'Single',
            'nationality' => 'Indian',
            'occupation' => 'Software Engineer',
            'source_of_income' => 'Salary',
            'annual_income' => '500000-1000000',
            'street' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'State',
            'pincode' => '123456',
            'id_type' => 'Aadhaar',
            'id_number' => '123456789012',
            'account_type' => 'savings',
            'initial_deposit' => 1000,
            'txn_pin' => '1234',
        ];
    }

    public function test_complete_profile_successfully() {
        $customerId = UserFactory::createCustomer();
        $db = \App\Shared\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $session = [
            'role' => 'customer', 'user_id' => $user['id'],
            'user_email' => $user['email'],
            'user_name' => $user['full_name'],
            'csrf_token' => 'test-csrf-token'
        ];

        $postData = $this->getValidData();
        $postData['csrf_token'] = 'test-csrf-token';

        $_FILES = [
            'profile_picture' => ['error' => UPLOAD_ERR_OK, 'size' => 1024, 'tmp_name' => $this->dummyPng, 'name' => 'profile.png'],
            'id_front' => ['error' => UPLOAD_ERR_OK, 'size' => 1024, 'tmp_name' => $this->dummyPng, 'name' => 'front.png'],
            'id_back' => ['error' => UPLOAD_ERR_OK, 'size' => 1024, 'tmp_name' => $this->dummyPng, 'name' => 'back.png'],
        ];

        try {
            RequestHelper::postJson('/User/complete-profile.php', $postData, $session);
            $this->fail("Expected Exception was not thrown");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(200, $response['status']);
            $this->assertTrue($response['success']);
            $this->assertEquals('Your profile has been submitted for review. We will notify you once it is processed.', $response['message']);
            
            // Verify db state
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $updatedUser = $stmt->fetch();
            
            $this->assertEquals('under_review', $updatedUser['status']);
            $this->assertNotEmpty($updatedUser['profile_picture']);
            $this->assertNotEmpty($updatedUser['id_front_path']);
            $this->assertNotEmpty($updatedUser['id_back_path']);
            $this->assertEquals('123456789012', $updatedUser['id_number']);
            
            $stmt = $this->db->prepare("SELECT * FROM kyc_applications WHERE user_id = ?");
            $stmt->execute([$user['customer_id']]);
            $kyc = $stmt->fetch();
            $this->assertNotEmpty($kyc);
            $this->assertEquals('submitted', $kyc['status']);
            
            $stmt = $this->db->prepare("SELECT * FROM accounts WHERE user_id = ? AND status = 'pending'");
            $stmt->execute([$user['customer_id']]);
            $account = $stmt->fetch();
            $this->assertNotEmpty($account);
            $this->assertEquals('savings', $account['account_type']);
        }
    }

    public function test_complete_profile_validation_errors() {
        $customerId = UserFactory::createCustomer();
        $db = \App\Shared\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $session = [
            'role' => 'customer', 'user_id' => $user['id'],
            'user_email' => $user['email'],
            'user_name' => $user['full_name'],
            'csrf_token' => 'test-csrf-token'
        ];

        $postData = $this->getValidData();
        $postData['csrf_token'] = 'test-csrf-token';
        $postData['dob'] = date('Y-m-d', strtotime('-17 years')); // Underage
        $postData['txn_pin'] = '123'; // Invalid PIN length

        $_FILES = []; // Missing files

        try {
            RequestHelper::postJson('/User/complete-profile.php', $postData, $session);
            $this->fail("Expected Exception was not thrown");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(422, $response['status']);
            $this->assertFalse($response['success']);
            
            $errors = $response['errors'];
            $this->assertArrayHasKey('dob', $errors);
            $this->assertArrayHasKey('txn_pin', $errors);
            $this->assertArrayHasKey('profile_picture', $errors);
            $this->assertArrayHasKey('id_front', $errors);
            $this->assertArrayHasKey('id_back', $errors);
        }
    }

    public function test_complete_profile_fails_invalid_csrf() {
        $customerId = UserFactory::createCustomer();
        $db = \App\Shared\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $session = [
            'role' => 'customer', 'user_id' => $user['id'],
            'user_email' => $user['email'],
            'user_name' => $user['full_name'],
            'csrf_token' => 'valid-token'
        ];

        $postData = $this->getValidData();
        $postData['csrf_token'] = 'invalid-token';

        try {
            RequestHelper::postJson('/User/complete-profile.php', $postData, $session);
            $this->fail("Expected Exception was not thrown");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(403, $response['status']);
            $this->assertFalse($response['success']);
            $this->assertEquals('Invalid security token', $response['message']);
        }
    }
}
