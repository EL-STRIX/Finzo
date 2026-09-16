<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use Tests\Factories\UserFactory;
use App\Application\Actions\ProcessTransferAction;
use App\Application\DTOs\TransferRequestDTO;
use App\Domain\Contracts\Services\UserServiceInterface;
use App\Domain\Contracts\Security\SecurityServiceInterface;
use App\Shared\Core\Exceptions\ApiException;
use App\Shared\Core\Exceptions\ResourceNotFoundException;
use App\Shared\Core\Exceptions\BusinessRuleException;
use PDO;

class ProcessTransferActionTest extends TestCase
{
    private $userServiceMock;
    private $securityMock;
    private ProcessTransferAction $action;
    private string $testCustomerId;
    private string $testAccountNo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userServiceMock = $this->createMock(UserServiceInterface::class);
        $this->securityMock = $this->createMock(SecurityServiceInterface::class);
        $this->action = new ProcessTransferAction($this->userServiceMock, $this->securityMock);

        // Ensure a valid user and account exists for PIN verification in tests
        $this->testCustomerId = UserFactory::createCustomer();
        $this->testAccountNo = UserFactory::createAccount($this->testCustomerId, [
            'txn_pin_hash' => password_hash('1234', PASSWORD_BCRYPT)
        ]);
    }

    public function testInvalidPinThrowsApiException(): void
    {
        $dto = new TransferRequestDTO(1, [
            'to_account_no' => 'FNZ2000002',
            'amount' => 100.0,
            'description' => 'Transfer',
            'txn_pin' => '0000'
        ]);

        $this->userServiceMock->method('getDb')->willReturn($this->db);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid transaction PIN');

        $this->action->execute($dto);
    }

    public function testSourceAccountNotFoundThrowsException(): void
    {
        $this->userServiceMock->method('getDb')->willReturn($this->db);
        $this->userServiceMock->method('getActiveAccount')->willReturn(null);

        $userRow = $this->db->query("SELECT id FROM users WHERE customer_id = '{$this->testCustomerId}'")->fetch();
        $actualId = (int)$userRow['id'];

        $dto = new TransferRequestDTO($actualId, [
            'to_account_no' => 'FNZ2000002',
            'amount' => 100.0,
            'description' => 'Transfer',
            'txn_pin' => '1234'
        ]);

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Source account not found or inactive');

        $this->action->execute($dto);
    }

    public function testSameAccountTransferThrowsBusinessRuleException(): void
    {
        $this->userServiceMock->method('getDb')->willReturn($this->db);
        $this->userServiceMock->method('getActiveAccount')->willReturn(['id' => 5, 'account_no' => $this->testAccountNo]);
        $this->userServiceMock->method('getAccountIdByNumber')->with($this->testAccountNo)->willReturn(5);

        $userRow = $this->db->query("SELECT id FROM users WHERE customer_id = '{$this->testCustomerId}'")->fetch();
        $actualId = (int)$userRow['id'];

        $dto = new TransferRequestDTO($actualId, [
            'to_account_no' => $this->testAccountNo,
            'amount' => 100.0,
            'description' => 'Transfer',
            'txn_pin' => '1234'
        ]);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Cannot transfer to your own account');

        $this->action->execute($dto);
    }
}
