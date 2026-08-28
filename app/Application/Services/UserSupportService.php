<?php

namespace App\Application\Services;

use App\Infrastructure\Repositories\UserSupportRepository;
use App\Shared\Core\Exceptions\ApiException;

class UserSupportService
{
    private UserSupportRepository $repository;

    public function __construct(UserSupportRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getUserTickets(int $userId): array
    {
        return $this->repository->getTickets($userId);
    }

    public function createTicket(int $userId, string $category, string $subject, string $priority, string $initialMessage): array
    {
        if (empty($subject) || empty($initialMessage)) {
            throw new ApiException('Subject and Description are required to raise a ticket.', 400);
        }

        $ticketNo = 'TICK' . date('Ymd') . rand(1000, 9999);
        $ticketId = $this->repository->createTicket($userId, $ticketNo, $category, $subject, $priority);
        $this->repository->addMessage($ticketId, 'user', $userId, $initialMessage);

        return ['success' => true, 'message' => 'Support ticket created successfully!', 'ticket_no' => $ticketNo];
    }

    public function getTicketThread(int $userId, int $ticketId): array
    {
        $ticket = $this->repository->getTicketById($ticketId, $userId);
        if (!$ticket) {
            throw new ApiException('Support ticket not found.', 404);
        }
        $messages = $this->repository->getMessages($ticketId);

        return ['success' => true, 'ticket' => $ticket, 'messages' => $messages];
    }

    public function replyTicket(int $userId, int $ticketId, string $message): array
    {
        $ticket = $this->repository->getTicketById($ticketId, $userId);
        if (!$ticket) {
            throw new ApiException('Support ticket not found.', 404);
        }
        if (empty($message)) {
            throw new ApiException('Reply message cannot be empty.', 400);
        }

        $this->repository->addMessage($ticketId, 'user', $userId, $message);
        return ['success' => true, 'message' => 'Reply posted successfully.'];
    }
}
