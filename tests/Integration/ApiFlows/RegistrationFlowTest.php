<?php
namespace Tests\Integration\ApiFlows;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use App\Infrastructure\Repositories\AuthRepository;
use App\Application\Services\AuthService;
use App\Shared\Core\Exceptions\TestResponseException;
use App\Shared\Core\Exceptions\ApiException;

class RegistrationFlowTest extends TestCase
{
    private AuthRepository $authRepo;
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authRepo = new AuthRepository($this->db);
        $this->authService = new AuthService($this->authRepo);
    }

    public function testServiceUserRegistrationWithVariousPhoneFormats()
    {
        $unique = time() . rand(1000, 9999);
        
        // 1. Standard 10 digits
        $phone1 = '98' . rand(10000000, 99999999);
        $res1 = $this->authService->registerUser('Test User 1', "user1_{$unique}@example.com", $phone1, 'Password123!');
        $this->assertTrue($res1);
        $this->assertEquals('pending', $_SESSION['user_status']);
        $this->assertNotEmpty($_SESSION['user_customer_id']);

        // 2. +91 with spaces
        $phone2 = '+91 97' . rand(10000000, 99999999);
        $res2 = $this->authService->registerUser('Test User 2', "user2_{$unique}@example.com", $phone2, 'Password123!');
        $this->assertTrue($res2);

        // 3. 91 without plus
        $phone3 = '9196' . rand(10000000, 99999999);
        $res3 = $this->authService->registerUser('Test User 3', "user3_{$unique}@example.com", $phone3, 'Password123!');
        $this->assertTrue($res3);
    }

    public function testDuplicateRegistrationFails()
    {
        $unique = time() . rand(1000, 9999);
        $email = "dup_{$unique}@example.com";
        $phone = '98' . rand(10000000, 99999999);

        $this->authService->registerUser('Dup User', $email, $phone, 'Password123!');

        $this->expectException(ApiException::class);
        $this->authService->registerUser('Dup User 2', $email, '95' . rand(10000000, 99999999), 'Password123!');
    }

    public function testApiUserRegistrationFlow()
    {
        $unique = time() . rand(1000, 9999);
        $registerData = [
            'csrf_token' => 'dummy_csrf_token',
            'full_name' => 'API Registered User',
            'email' => "api_reg_{$unique}@example.com",
            'phone' => '+91 98' . rand(10000000, 99999999),
            'password' => 'SecurePass123!',
            'confirm_password' => 'SecurePass123!',
            'terms' => 'on'
        ];

        try {
            RequestHelper::postJson('api/v1/auth/register', $registerData);
            $this->fail("Should throw TestResponseException in testing mode");
        } catch (TestResponseException $e) {
            $data = json_decode($e->getMessage(), true);
            $this->assertTrue($data['success'] ?? false);
            $this->assertEquals(200, $data['status'] ?? 0);
            $this->assertStringContainsString('complete-profile.php', $data['redirect'] ?? '');
        }
    }
}
