<?php
namespace Tests\Unit\Validators;

use Tests\TestCase;
use App\Presentation\Validators\UserValidator;
use App\Application\Services\UserService;
use App\Domain\Exceptions\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;

class UserValidatorTest extends TestCase {
    
    /** @var UserService&MockObject */
    private $userServiceMock;
    private UserValidator $validator;

    protected function setUp(): void {
        parent::setUp();
        
        // validate_phone is a global function in functions.php
        if (!defined('FINZO_ROOT')) {
            define('FINZO_ROOT', dirname(dirname(dirname(__DIR__))));
        }
        require_once FINZO_ROOT . '/config/functions.php';
        
        $this->userServiceMock = $this->createMock(UserService::class);
        $this->validator = new UserValidator($this->userServiceMock);
    }
    
    public function testValidateProfileUpdatePassesValidData() {
        $data = [
            'full_name' => 'Valid Name',
            'phone' => '9876543210',
            'email' => 'test@example.com'
        ];
        
        $this->userServiceMock->expects($this->once())
            ->method('checkEmailExists')
            ->willReturn(false);
            
        $this->userServiceMock->expects($this->once())
            ->method('checkPhoneExists')
            ->willReturn(false);
            
        $result = $this->validator->validateProfileUpdate($data, 1, 'old@example.com');
        
        $this->assertEquals('Valid Name', $result['full_name']);
        $this->assertEquals('9876543210', $result['phone']);
        $this->assertEquals('test@example.com', $result['email']);
    }
    
    public function testValidateProfileUpdateFailsOnShortName() {
        $data = [
            'full_name' => 'A'
        ];
        
        $this->expectException(ValidationException::class);
        
        try {
            $this->validator->validateProfileUpdate($data, 1, 'old@example.com');
        } catch (ValidationException $e) {
            $errors = json_decode($e->getMessage(), true);
            $this->assertArrayHasKey('full_name', $errors);
            throw $e;
        }
    }

    public function testValidateProfileUpdateFailsOnInvalidPhone() {
        $data = [
            'full_name' => 'Valid Name',
            'phone' => '123'
        ];
        
        $this->expectException(ValidationException::class);
        
        try {
            $this->validator->validateProfileUpdate($data, 1, 'old@example.com');
        } catch (ValidationException $e) {
            $errors = json_decode($e->getMessage(), true);
            $this->assertArrayHasKey('phone', $errors);
            throw $e;
        }
    }

    public function testValidateProfileUpdateFailsOnExistingEmail() {
        $data = [
            'full_name' => 'Valid Name',
            'email' => 'new@example.com'
        ];
        
        $this->userServiceMock->expects($this->once())
            ->method('checkEmailExists')
            ->with('new@example.com', 1)
            ->willReturn(true);
            
        $this->expectException(ValidationException::class);
        
        try {
            $this->validator->validateProfileUpdate($data, 1, 'old@example.com');
        } catch (ValidationException $e) {
            $errors = json_decode($e->getMessage(), true);
            $this->assertArrayHasKey('email', $errors);
            throw $e;
        }
    }
}
