<?php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\UserService;
use App\Repositories\UserRepository;

class UserServiceKycTest extends TestCase {
    protected UserService $service;
    protected string $dummyPng;
    protected string $dummyTxt;

    protected function setUp(): void {
        parent::setUp();
        
        $this->service = new UserService(new UserRepository());
        
        $this->dummyPng = tempnam(sys_get_temp_dir(), 'png');
        file_put_contents($this->dummyPng, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAACklEQVR4nGMAAQAABQABDQottAAAAABJRU5ErkJggg=='));

        $this->dummyTxt = tempnam(sys_get_temp_dir(), 'txt');
        file_put_contents($this->dummyTxt, 'Hello World');
    }

    protected function tearDown(): void {
        if (file_exists($this->dummyPng)) unlink($this->dummyPng);
        if (file_exists($this->dummyTxt)) unlink($this->dummyTxt);
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
            'has_profile_pic' => false,
            'has_id_front' => false,
            'has_id_back' => false
        ];
    }

    private function getValidFiles(): array {
        return [
            'profile_picture' => ['error' => UPLOAD_ERR_OK, 'size' => 1024, 'tmp_name' => $this->dummyPng],
            'id_front' => ['error' => UPLOAD_ERR_OK, 'size' => 1024, 'tmp_name' => $this->dummyPng],
            'id_back' => ['error' => UPLOAD_ERR_OK, 'size' => 1024, 'tmp_name' => $this->dummyPng],
        ];
    }

    public function test_valid_kyc_data_returns_no_errors() {
        $errors = $this->service->validateKyc($this->getValidData(), $this->getValidFiles());
        $this->assertEmpty($errors);
    }

    public function test_age_must_be_at_least_18() {
        $data = $this->getValidData();
        $data['dob'] = date('Y-m-d', strtotime('-17 years'));
        
        $errors = $this->service->validateKyc($data, $this->getValidFiles());
        $this->assertArrayHasKey('dob', $errors);
        $this->assertEquals('You must be at least 18 years old', $errors['dob']);
    }

    public function test_missing_required_fields_return_errors() {
        $data = $this->getValidData();
        $data['father_name'] = '';
        $data['gender'] = 'InvalidGender';
        $data['pincode'] = '123';
        $data['id_type'] = 'InvalidID';
        
        $errors = $this->service->validateKyc($data, $this->getValidFiles());
        
        $this->assertArrayHasKey('father_name', $errors);
        $this->assertArrayHasKey('gender', $errors);
        $this->assertArrayHasKey('pincode', $errors);
        $this->assertArrayHasKey('id_type', $errors);
    }

    public function test_missing_files_when_no_existing_files() {
        $data = $this->getValidData();
        $files = [
            'profile_picture' => ['error' => UPLOAD_ERR_NO_FILE],
            'id_front' => ['error' => UPLOAD_ERR_NO_FILE],
            'id_back' => ['error' => UPLOAD_ERR_NO_FILE],
        ];

        $errors = $this->service->validateKyc($data, $files);
        
        $this->assertArrayHasKey('profile_picture', $errors);
        $this->assertArrayHasKey('id_front', $errors);
        $this->assertArrayHasKey('id_back', $errors);
    }

    public function test_missing_files_allowed_if_has_existing() {
        $data = $this->getValidData();
        $data['has_profile_pic'] = true;
        $data['has_id_front'] = true;
        $data['has_id_back'] = true;

        $files = [
            'profile_picture' => ['error' => UPLOAD_ERR_NO_FILE],
            'id_front' => ['error' => UPLOAD_ERR_NO_FILE],
            'id_back' => ['error' => UPLOAD_ERR_NO_FILE],
        ];

        $errors = $this->service->validateKyc($data, $files);
        
        $this->assertArrayNotHasKey('profile_picture', $errors);
        $this->assertArrayNotHasKey('id_front', $errors);
        $this->assertArrayNotHasKey('id_back', $errors);
    }

    public function test_file_size_exceeds_limit() {
        $files = $this->getValidFiles();
        $files['profile_picture']['size'] = MAX_UPLOAD_SIZE + 1; // Over 5MB

        $errors = $this->service->validateKyc($this->getValidData(), $files);
        $this->assertArrayHasKey('profile_picture', $errors);
        $this->assertEquals('Profile photo must be less than 5MB', $errors['profile_picture']);
    }

    public function test_file_type_must_be_image() {
        $files = $this->getValidFiles();
        $files['id_front']['tmp_name'] = $this->dummyTxt;

        $errors = $this->service->validateKyc($this->getValidData(), $files);
        $this->assertArrayHasKey('id_front', $errors);
        $this->assertEquals('Front image must be JPG or PNG format', $errors['id_front']);
    }
    
    public function test_invalid_initial_deposit() {
        $data = $this->getValidData();
        $data['initial_deposit'] = 100; // MIN_INITIAL_DEPOSIT is 500

        $errors = $this->service->validateKyc($data, $this->getValidFiles());
        $this->assertArrayHasKey('initial_deposit', $errors);
        $this->assertStringContainsString('Initial deposit must be at least', $errors['initial_deposit']);
    }

    public function test_invalid_txn_pin() {
        $data = $this->getValidData();
        $data['txn_pin'] = '12A4'; // Not numeric

        $errors = $this->service->validateKyc($data, $this->getValidFiles());
        $this->assertArrayHasKey('txn_pin', $errors);
        $this->assertEquals('Transaction PIN must be exactly 4 digits', $errors['txn_pin']);
    }
}
