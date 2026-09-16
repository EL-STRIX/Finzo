<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\UserSecurityService;
use App\Infrastructure\Repositories\UserSecurityRepository;
use App\Shared\Core\Exceptions\ApiException;

class UserSecurityServiceTest extends TestCase
{
    private $repositoryMock;
    private UserSecurityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = $this->createMock(UserSecurityRepository::class);
        $this->service = new UserSecurityService($this->repositoryMock);
    }

    public function testGetSecurityOverview(): void
    {
        $this->repositoryMock->method('getSecuritySettings')->with(1)->willReturn([
            'two_factor_enabled' => 1,
            'transaction_pin_hash' => '$2y$10$hash',
            'login_notifications_enabled' => 1
        ]);
        $this->repositoryMock->method('getActiveSessions')->with(1)->willReturn([
            ['id' => 10, 'ip_address' => '127.0.0.1', 'device_name' => 'Chrome']
        ]);

        $result = $this->service->getSecurityOverview(1);

        $this->assertTrue($result['two_factor_enabled']);
        $this->assertTrue($result['has_transaction_pin']);
        $this->assertTrue($result['login_notifications_enabled']);
        $this->assertCount(1, $result['sessions']);
    }

    public function testSetTransactionPinInvalidLengthThrowsException(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Transaction PIN must be exactly 4 numeric digits.');

        $this->service->setTransactionPin(1, '123');
    }

    public function testSetTransactionPinNonNumericThrowsException(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Transaction PIN must be exactly 4 numeric digits.');

        $this->service->setTransactionPin(1, 'abcd');
    }

    public function testSetTransactionPinSuccess(): void
    {
        $this->repositoryMock->expects($this->once())
            ->method('setTransactionPin')
            ->with(1, $this->callback(fn($hash) => password_verify('4321', $hash)));

        $result = $this->service->setTransactionPin(1, '4321');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('successfully', $result['message']);
    }

    public function testToggle2fa(): void
    {
        $this->repositoryMock->method('getSecuritySettings')->with(1)->willReturn([]);
        $this->repositoryMock->expects($this->once())
            ->method('createOrUpdateSettings')
            ->with(1, $this->callback(fn($data) => $data['two_factor_enabled'] === 1));

        $result = $this->service->toggle2FA(1, true);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['two_factor_enabled']);
        $this->assertNotEmpty($result['secret']);
    }
}
