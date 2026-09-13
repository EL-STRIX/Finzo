<?php
namespace Tests\Unit\Validators;

use Tests\TestCase;
use App\Presentation\Validators\UserValidator;
use App\Application\Services\UserService;
use App\Shared\Core\Exceptions\ValidationException;

class UserTransferValidatorTest extends TestCase
{
    private UserValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $userService = $this->createMock(UserService::class);
        $this->validator = new UserValidator($userService);
    }

    public function testValidateTransferSuccess(): void
    {
        $input = [
            'to_account_no' => 'fnz20260002',
            'amount' => '1500.50',
            'description' => 'Rent payment',
            'txn_pin' => '1234'
        ];

        $result = $this->validator->validateTransfer($input);

        $this->assertSame('FNZ20260002', $result['to_account_no']);
        $this->assertSame(1500.50, $result['amount']);
        $this->assertSame('Rent payment', $result['description']);
        $this->assertSame('1234', $result['txn_pin']);
    }

    public function testValidateTransferWithFallbackKeys(): void
    {
        $input = [
            'to_account' => ' FNZ9999 ',
            'amount' => 500,
            'pin' => '4321'
        ];

        $result = $this->validator->validateTransfer($input);

        $this->assertSame('FNZ9999', $result['to_account_no']);
        $this->assertSame(500.0, $result['amount']);
        $this->assertSame('4321', $result['txn_pin']);
    }

    public function testValidateTransferFailsOnMissingRecipient(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validateTransfer([
            'to_account_no' => '',
            'amount' => 100,
            'txn_pin' => '1234'
        ]);
    }

    public function testValidateTransferFailsOnZeroOrNegativeAmount(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validateTransfer([
            'to_account_no' => 'FNZ20260002',
            'amount' => 0,
            'txn_pin' => '1234'
        ]);
    }

    public function testValidateTransferFailsOnInvalidPinLength(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validateTransfer([
            'to_account_no' => 'FNZ20260002',
            'amount' => 100,
            'txn_pin' => '123'
        ]);
    }

    public function testValidateTransferFailsOnNonNumericPin(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validateTransfer([
            'to_account_no' => 'FNZ20260002',
            'amount' => 100,
            'txn_pin' => 'abcd'
        ]);
    }
}
