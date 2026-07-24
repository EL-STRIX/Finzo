<?php
namespace Tests\Integration\Security;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;

class SqlInjectionProtectionTest extends TestCase {

    public function testAuthenticationSqlInjectionIsNeutralized() {
        // Seed an admin or user to ensure the table isn't empty
        UserFactory::createCustomer([
            'email' => 'legit@example.com',
            'password' => 'SafePass123!'
        ]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $payloads = [
            'admin\' OR 1=1 --',
            '" OR ""="',
            '\' OR \'1\'=\'1',
            'admin@example.com\' UNION SELECT * FROM users --'
        ];
        
        foreach ($payloads as $payload) {
            $postData = [
                'identity' => $payload,
                'password' => 'random_password',
                'csrf_token' => 'dummy_token'
            ];
            
            try {
                RequestHelper::postJson('api/v1/auth/login', $postData, $session);
                $this->fail("SQL injection payload successfully logged in: " . $payload);
            } catch (\Exception $e) {
                $response = json_decode($e->getMessage(), true);
                $this->assertEquals(401, $response['status'] ?? $e->getCode(), "Payload: $payload");
            }
        }
    }

    public function testRouteParameterSqlInjectionIsNeutralized() {
        $adminId = 1; // Assuming system seeder creates an admin with ID 1
        $session = [
            'user_id' => $adminId,
            'user_customer_id' => 'ADMIN001',
            'role' => 'admin'
        ];
        
        $payloads = [
            '1 OR 1=1',
            '1; DROP TABLE users',
            '1 UNION SELECT null, null'
        ];

        foreach ($payloads as $payload) {
            try {
                RequestHelper::getJson('api/v1/users/' . urlencode($payload), [], $session);
                $this->fail("SQL injection payload succeeded: " . $payload);
            } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
                $response = json_decode($e->getMessage(), true);
                $this->assertContains($response['status'], [404, 400]);
            } catch (\App\Shared\Core\Exceptions\ApiException $e) {
                $this->assertContains($e->getStatusCode(), [404, 400], "Payload: $payload");
            }
        }
    }
}
