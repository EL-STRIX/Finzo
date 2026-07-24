<?php
namespace Tests\Feature\Auth;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;

class StaffLoginTest extends TestCase {
    
    public function testStaffLoginSuccess() {
        $password = 'Secret123!';
        $staffId = UserFactory::createStaff([
            'email' => 'staff_login@example.com',
            'password' => $password
        ]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'identity' => 'staff_login@example.com',
            'password' => $password,
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('auth/staff-login.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(200, $response['status']);
            $this->assertTrue($response['success']);
            
            // Check session
            $this->assertTrue(isset($_SESSION['staff_id']));
            $this->assertEquals('staff', $_SESSION['role']);
        }
    }

    public function testStaffLoginFailureWithInvalidPassword() {
        UserFactory::createStaff([
            'email' => 'staff_wrong@example.com',
            'password' => 'Correct123!'
        ]);
        
        $session = ['csrf_token' => 'dummy_token'];
        $postData = [
            'identity' => 'staff_wrong@example.com',
            'password' => 'Wrong123!',
            'csrf_token' => 'dummy_token'
        ];
        
        try {
            RequestHelper::postJson('auth/staff-login.php', $postData, $session);
            $this->fail("json_response did not throw an exception");
        } catch (\Exception $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertEquals(401, $response['status']);
            $this->assertFalse($response['success']);
        }
    }
}
