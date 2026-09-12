<?php

namespace App\Presentation\Api\V1\User;

use App\Application\Services\UserSupportService;
use App\Shared\Core\Exceptions\ApiException;

class UserSupportController
{
    private UserSupportService $service;

    public function __construct(UserSupportService $service)
    {
        $this->service = $service;
    }

    public function tickets(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }
        return ['success' => true, 'tickets' => $this->service->getUserTickets($userId)];
    }

    public function createTicket(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }

        $category = (string)($_POST['category'] ?? 'General Inquiry');
        $subject = (string)($_POST['subject'] ?? '');
        $priority = (string)($_POST['priority'] ?? 'medium');
        $message = (string)($_POST['message'] ?? '');

        return $this->service->createTicket($userId, $category, $subject, $priority, $message);
    }

    public function ticketDetail(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }
        $ticketId = (int)($id ?? $_GET['ticket_id'] ?? 0);
        return $this->service->getTicketThread($userId, $ticketId);
    }

    public function reply(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }
        $ticketId = (int)($id ?? $_POST['ticket_id'] ?? 0);
        $message = (string)($_POST['message'] ?? '');

        return $this->service->replyTicket($userId, $ticketId, $message);
    }
}
