<?php
namespace Tests\Unit\Validators;

use Tests\TestCase;
use App\Presentation\Validators\UserValidator;
use App\Application\Services\UserService;
use App\Shared\Core\Exceptions\ValidationException;

class UserPinValidatorTest extends TestCase
{
    private UserValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $userService = $this->createMock(UserService::class);
        $this->validator = new UserValidator($userService);
    }

    public function testValidatePinChangeSuccessWithCurrentPin(): void
    {
        $input = [
            'auth_type' => 'pin',
            'current_pin' => '1234',
            'new_pin' => '5678',
            'confirm_pin' => '5678'
        ];

        $result = $this->validator->validatePinChange($input);

        $this->assertSame('pin', $result['auth_type']);
        $this->assertSame('1234', $result['current_pin']);
        $this->assertSame('5678', $result['new_pin']);
        $this->assertSame('5678', $result['confirm_pin']);
    }

    public function testValidatePinChangeSuccessWithPassword(): void
    {
        $input = [
            'auth_type' => 'password',
            'current_password' => 'SecretPassword123!',
            'new_pin' => '9988',
            'confirm_pin' => '9988'
        ];

        $result = $this->validator->validatePinChange($input);

        $this->assertSame('password', $result['auth_type']);
        $this->assertSame('SecretPassword123!', $result['current_password']);
        $this->assertSame('9988', $result['new_pin']);
    }

    public function testValidatePinChangeFailsOnInvalidCurrentPin(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validatePinChange([
            'auth_type' => 'pin',
            'current_pin' => '12',
            'new_pin' => '5678',
            'confirm_pin' => '5678'
        ]);
    }

    public function testValidatePinChangeFailsOnMissingPassword(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validatePinChange([
            'auth_type' => 'password',
            'current_password' => '',
            'new_pin' => '5678',
            'confirm_pin' => '5678'
        ]);
    }

    public function testValidatePinChangeFailsOnNonDigitNewPin(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validatePinChange([
            'auth_type' => 'pin',
            'current_pin' => '1234',
            'new_pin' => 'abcd',
            'confirm_pin' => 'abcd'
        ]);
    }

    public function testValidatePinChangeFailsOnMismatchedConfirmPin(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validatePinChange([
            'auth_type' => 'pin',
            'current_pin' => '1234',
            'new_pin' => '5678',
            'confirm_pin' => '8765'
        ]);
    }

    public function testValidatePinChangeFailsWhenNewPinSameAsCurrentPin(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validatePinChange([
            'auth_type' => 'pin',
            'current_pin' => '1234',
            'new_pin' => '1234',
            'confirm_pin' => '1234'
        ]);
    }
}
