<?php
namespace Tests\Integration\Security;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;

class CsrfMiddlewareTest extends TestCase {

    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER['HTTP_X_ENFORCE_CSRF'] = 'true';
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_X_ENFORCE_CSRF']);
        parent::tearDown();
    }

    public function testPostRequestsAreBlockedWithoutCsrfToken() {
        $email = 'csrf_test@example.com';
        UserFactory::createCustomer([
            'email' => $email,
            'password' => 'SafePass123!'
        ]);
        
        $postData = [
            'identity' => $email,
            'password' => 'SafePass123!'
            // Intentionally omitting csrf_token
        ];
        
        $session = []; // No session or token
        
        try {
            RequestHelper::postJson('api/v1/auth/login', $postData, $session);
            $this->fail("Request succeeded without CSRF token");
        } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(403, $response['status']);
        } catch (\App\Shared\Core\Exceptions\ApiException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(403, $response['status'] ?? $e->getCode());
        }
    }

    public function testPostRequestsSucceedWithValidCsrfToken() {
        $email = 'csrf_test2@example.com';
        UserFactory::createCustomer([
            'email' => $email,
            'password' => 'SafePass123!'
        ]);
        
        $token = 'valid_test_token_123';
        $postData = [
            'identity' => $email,
            'password' => 'SafePass123!',
            'csrf_token' => $token
        ];
        
        $session = [
            'csrf_token' => $token
        ];
        
        try {
            RequestHelper::postJson('api/v1/auth/login', $postData, $session);
            $this->fail("RequestHelper should have thrown TestResponseException with JSON");
        } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertTrue($response['success']);
            $this->assertEquals(200, $response['status']);
        } catch (\Exception $e) {
            $this->fail("Request failed with valid CSRF token: " . $e->getMessage());
        }
    }
}
