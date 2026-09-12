<?php

namespace Tests\Integration\ApiFlows;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\UserFactory;

class SupportFlowTest extends TestCase
{
    private array $userSession;
    private array $adminSession;
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $customerId = UserFactory::createCustomer([
            'email' => 'support_test_' . uniqid() . '@example.com',
            'full_name' => 'Support Test User'
        ]);

        $db = \App\Shared\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM users WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $this->userId = (int)$stmt->fetchColumn();

        $this->userSession = [
            'user_id' => $this->userId,
            'role' => 'customer',
            'user_role' => 'customer',
            'user_status' => 'active',
            'csrf_token' => 'dummy_csrf_token'
        ];

        $this->adminSession = [
            'admin_id' => 1,
            'role' => 'admin',
            'admin_role' => 'admin',
            'csrf_token' => 'dummy_csrf_token'
        ];
    }

    private function unwrap(callable $fn): array
    {
        try {
            $res = $fn();
            if (is_array($res)) return $res;
            if (is_string($res)) {
                $decoded = json_decode($res, true);
                if (is_array($decoded)) return $decoded;
            }
            return [];
        } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
            $decoded = json_decode($e->getMessage(), true);
            if (is_array($decoded) && isset($decoded['status']) && (int)$decoded['status'] >= 400) {
                throw $e;
            }
            if (is_array($decoded) && isset($decoded['success']) && $decoded['success'] === false) {
                throw $e;
            }
            return $decoded ?: [];
        }
    }

    public function testEndToEndSupportTicketFlow(): void
    {
        // 1. User creates a support ticket
        $createData = [
            'category' => 'Transaction Dispute',
            'priority' => 'high',
            'subject' => 'Disputed POS charge of 500',
            'message' => 'Please investigate this unauthorized merchant debit.',
            'csrf_token' => 'dummy_csrf_token'
        ];

        $createRes = $this->unwrap(fn() => RequestHelper::postJson('api/v1/user/support/tickets', $createData, $this->userSession));
        $this->assertTrue($createRes['success'] ?? false);
        $this->assertNotEmpty($createRes['ticket_no'] ?? null);

        // 2. User lists tickets
        $userTicketsRes = $this->unwrap(fn() => RequestHelper::getJson('api/v1/user/support/tickets', [], $this->userSession));
        $this->assertTrue($userTicketsRes['success'] ?? false);
        $this->assertNotEmpty($userTicketsRes['tickets'] ?? []);
        $ticketId = (int)$userTicketsRes['tickets'][0]['id'];

        // 3. Admin lists all support tickets
        $adminTicketsRes = $this->unwrap(fn() => RequestHelper::getJson('api/v1/admin/support/tickets', [], $this->adminSession));
        $this->assertTrue($adminTicketsRes['success'] ?? false);
        $this->assertGreaterThanOrEqual(1, $adminTicketsRes['total'] ?? 0);
        $this->assertGreaterThanOrEqual(1, $adminTicketsRes['stats']['open_count'] ?? 0);

        // 4. Admin views ticket detail
        $adminDetailRes = $this->unwrap(fn() => RequestHelper::getJson("api/v1/admin/support/tickets/{$ticketId}", [], $this->adminSession));
        $this->assertTrue($adminDetailRes['success'] ?? false);
        $this->assertEquals($ticketId, $adminDetailRes['ticket']['id'] ?? null);
        $this->assertCount(1, $adminDetailRes['messages'] ?? []);

        // 5. Admin replies to ticket
        $replyData = [
            'message' => 'We have credited your account temporarily while investigating.',
            'status' => 'in_progress',
            'csrf_token' => 'dummy_csrf_token'
        ];
        $adminReplyRes = $this->unwrap(fn() => RequestHelper::postJson("api/v1/admin/support/tickets/{$ticketId}/reply", $replyData, $this->adminSession));
        $this->assertTrue($adminReplyRes['success'] ?? false);

        // 6. User reads thread and sees admin reply
        $userThreadRes = $this->unwrap(fn() => RequestHelper::getJson("api/v1/user/support/tickets/{$ticketId}", [], $this->userSession));
        $this->assertTrue($userThreadRes['success'] ?? false);
        $this->assertCount(2, $userThreadRes['messages'] ?? []);
        $this->assertEquals('admin', $userThreadRes['messages'][1]['sender_type'] ?? null);

        // 7. Admin resolves ticket
        $statusData = [
            'status' => 'resolved',
            'csrf_token' => 'dummy_csrf_token'
        ];
        $adminStatusRes = $this->unwrap(fn() => RequestHelper::postJson("api/v1/admin/support/tickets/{$ticketId}/status", $statusData, $this->adminSession));
        $this->assertTrue($adminStatusRes['success'] ?? false);

        // 8. Verify ticket status is resolved
        $finalCheck = $this->unwrap(fn() => RequestHelper::getJson("api/v1/admin/support/tickets/{$ticketId}", [], $this->adminSession));
        $this->assertEquals('resolved', $finalCheck['ticket']['status'] ?? null);
    }

    public function testUnauthorizedAccessIsBlocked(): void
    {
        // Non-admin (customer session) attempting to access admin support tickets endpoint
        $blocked = false;
        try {
            $this->unwrap(fn() => RequestHelper::getJson('api/v1/admin/support/tickets', [], $this->userSession));
        } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
            $decoded = json_decode($e->getMessage(), true);
            if (isset($decoded['status']) && in_array($decoded['status'], [401, 403])) {
                $blocked = true;
            }
        }
        $this->assertTrue($blocked, "Customer must not access admin support endpoints");

        // Guest session attempting to create user support ticket
        $guestBlocked = false;
        try {
            $this->unwrap(fn() => RequestHelper::postJson('api/v1/user/support/tickets', [
                'category' => 'General',
                'priority' => 'low',
                'subject' => 'Test',
                'message' => 'Test msg',
                'csrf_token' => 'dummy_csrf_token'
            ], []));
        } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
            $decoded = json_decode($e->getMessage(), true);
            if (isset($decoded['status']) && in_array($decoded['status'], [401, 403])) {
                $guestBlocked = true;
            }
        }
        $this->assertTrue($guestBlocked, "Unauthenticated guest must not create tickets");
    }
}
