<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\UserSupportService;
use App\Infrastructure\Repositories\UserSupportRepository;
use App\Shared\Core\Exceptions\ApiException;

class UserSupportServiceTest extends TestCase
{
    private $repositoryMock;
    private UserSupportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = $this->createMock(UserSupportRepository::class);
        $this->service = new UserSupportService($this->repositoryMock);
    }

    public function testCreateTicketEmptySubjectThrowsException(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Subject and Description are required to raise a ticket.');

        $this->service->createTicket(1, 'general', '', 'medium', 'Description here');
    }

    public function testCreateTicketSuccess(): void
    {
        $this->repositoryMock->expects($this->once())
            ->method('createTicket')
            ->willReturn(101);

        $this->repositoryMock->expects($this->once())
            ->method('addMessage')
            ->with(101, 'user', 1, 'My transfer is pending');

        $result = $this->service->createTicket(1, 'transactions', 'Transfer delay', 'high', 'My transfer is pending');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('ticket_no', $result);
    }

    public function testGetTicketThreadNotFoundThrows404(): void
    {
        $this->repositoryMock->method('getTicketById')->with(999, 1)->willReturn(false);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(404);

        $this->service->getTicketThread(1, 999);
    }

    public function testReplyTicketEmptyMessageThrowsException(): void
    {
        $this->repositoryMock->method('getTicketById')->with(101, 1)->willReturn(['id' => 101]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Reply message cannot be empty.');

        $this->service->replyTicket(1, 101, '');
    }
}
