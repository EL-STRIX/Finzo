<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Tests\Factories\UserFactory;
use App\Application\Services\NotificationService;

class NotificationServiceTest extends TestCase
{
    private NotificationService $service;
    private string $testCustomerId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificationService();

        $this->testCustomerId = UserFactory::createCustomer();
    }

    public function testSendNotificationInsertsRecord(): void
    {
        $result = $this->service->send($this->testCustomerId, 'transaction', 'Transfer Alert', 'You sent 500.00 INR');

        $this->assertTrue($result);

        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? AND title = ?");
        $stmt->execute([$this->testCustomerId, 'Transfer Alert']);
        $notif = $stmt->fetch();

        $this->assertNotEmpty($notif);
        $this->assertEquals('transaction', $notif['type']);
        $this->assertEquals('You sent 500.00 INR', $notif['message']);
    }
}
