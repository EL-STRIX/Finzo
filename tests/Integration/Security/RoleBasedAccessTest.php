<?php
namespace Tests\Integration\Security;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;

class RoleBasedAccessTest extends TestCase {

    public function testCustomerCannotAccessAdminRoutes() {
        $customerId = UserFactory::createCustomer();
        
        $session = [
            'user_id' => 999, // dummy ID
            'user_customer_id' => $customerId,
            'role' => 'customer'
        ];
        
        // Attempt to hit an Admin-only route
        try {
            RequestHelper::getJson('api/v1/admins', [], $session);
            $this->fail("Expected ApiException but route did not throw");
        } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(403, $response['status']);
        } catch (\App\Shared\Core\Exceptions\AuthorizationException $e) {
            $this->assertEquals(403, $e->getCode());
        } catch (\App\Shared\Core\Exceptions\ApiException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    public function testUnauthenticatedUserCannotAccessProtectedRoutes() {
        // No session
        $session = [];
        
        try {
            RequestHelper::getJson('api/v1/admins', [], $session);
            $this->fail("Expected ApiException but route did not throw");
        } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(401, $response['status']);
        } catch (\App\Shared\Core\Exceptions\AuthenticationException $e) {
            $this->assertEquals(401, $e->getCode());
        } catch (\App\Shared\Core\Exceptions\ApiException $e) {
            $this->assertEquals(401, $e->getStatusCode());
        }
    }
}
