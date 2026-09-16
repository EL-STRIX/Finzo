<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Tests\Factories\UserFactory;
use App\Application\Services\AdminSupportService;
use App\Infrastructure\Repositories\AdminSupportRepository;
use App\Shared\Core\Exceptions\ApiException;

class AdminSupportServiceTest extends TestCase
{
    private $repositoryMock;
    private AdminSupportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = $this->createMock(AdminSupportRepository::class);
        $this->service = new AdminSupportService($this->repositoryMock);
    }

    public function testListTicketsReturnsSummaryAndStats(): void
    {
        $this->repositoryMock->method('getTickets')->willReturn([
            ['id' => 1, 'subject' => 'Issue with OTP', 'status' => 'open']
        ]);
        $this->repositoryMock->method('getStats')->willReturn([
            'open_count' => 1,
            'resolved_count' => 10
        ]);

        $result = $this->service->listTickets();

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['tickets']);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(1, $result['stats']['open_count']);
    }

    public function testGetTicketDetailNotFoundThrows404(): void
    {
        $this->repositoryMock->method('getTicketById')->with(888)->willReturn(null);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(404);

        $this->service->getTicketDetail(888);
    }

    public function testReplyTicketEmptyMessageThrowsException(): void
    {
        $this->repositoryMock->method('getTicketById')->with(1)->willReturn([
            'id' => 1,
            'status' => 'open'
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Reply message cannot be empty.');

        $this->service->replyTicket(1, 1, '   ');
    }

    public function testUpdateStatusInvalidStatusThrows422(): void
    {
        $this->repositoryMock->method('getTicketById')->with(1)->willReturn([
            'id' => 1,
            'status' => 'open'
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Invalid status transition.');

        $this->service->updateStatus(1, 1, 'invalid_status_name');
    }

    public function testUpdateStatusSuccess(): void
    {
        $custId = UserFactory::createCustomer();

        $this->repositoryMock->method('getTicketById')->with(1)->willReturn([
            'id' => 1,
            'status' => 'open',
            'customer_id' => $custId,
            'ticket_no' => 'TICK1234',
            'subject' => 'Billing inquiry'
        ]);
        $this->repositoryMock->method('getDb')->willReturn($this->db);

        $this->repositoryMock->expects($this->once())
            ->method('updateStatus')
            ->with(1, 'resolved');

        $result = $this->service->updateStatus(1, 1, 'resolved');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Resolved', $result['message']);
    }
}
