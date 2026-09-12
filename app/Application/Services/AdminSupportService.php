<?php

namespace App\Application\Services;

use App\Infrastructure\Repositories\AdminSupportRepository;
use App\Shared\Core\Exceptions\ApiException;

class AdminSupportService
{
    private AdminSupportRepository $repository;

    public function __construct(AdminSupportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List tickets with live queue stats.
     */
    public function listTickets(?string $status = null, ?string $priority = null, ?string $search = null): array
    {
        $tickets = $this->repository->getTickets($status, $priority, $search);
        $stats = $this->repository->getStats();

        return [
            'success' => true,
            'tickets' => $tickets,
            'stats'   => $stats,
            'total'   => count($tickets)
        ];
    }

    /**
     * Get detailed ticket thread and customer information.
     */
    public function getTicketDetail(int $ticketId): array
    {
        $ticket = $this->repository->getTicketById($ticketId);
        if (!$ticket) {
            throw new ApiException('Support ticket not found.', 404);
        }

        $messages = $this->repository->getMessages($ticketId);

        return [
            'success'  => true,
            'ticket'   => $ticket,
            'messages' => $messages
        ];
    }

    /**
     * Post official admin reply and optionally transition ticket status.
     */
    public function replyTicket(int $adminId, int $ticketId, string $message, ?string $newStatus = null): array
    {
        $ticket = $this->repository->getTicketById($ticketId);
        if (!$ticket) {
            throw new ApiException('Support ticket not found.', 404);
        }

        $message = trim($message);
        if (empty($message)) {
            throw new ApiException('Reply message cannot be empty.', 400);
        }

        $this->repository->addMessage($ticketId, 'admin', $adminId, $message);

        // Status transition
        $targetStatus = $newStatus;
        if (empty($targetStatus) && $ticket['status'] === 'open') {
            $targetStatus = 'in_progress';
        }

        if (!empty($targetStatus) && in_array($targetStatus, ['open', 'in_progress', 'resolved', 'closed'], true)) {
            $this->repository->updateStatus($ticketId, $targetStatus);
        }

        // Notify customer
        if (function_exists('send_notification') && !empty($ticket['customer_id'])) {
            $snippet = mb_strimwidth($message, 0, 80, '...');
            send_notification(
                $this->repository->getDb(),
                $ticket['customer_id'],
                \App\Domain\Enums\NotificationTypeEnum::SYSTEM->value,
                "Support Reply: #{$ticket['ticket_no']}",
                "Our support team replied to your ticket: \"{$snippet}\""
            );
        }

        return [
            'success' => true,
            'message' => 'Official reply posted successfully.'
        ];
    }

    /**
     * Update ticket resolution status.
     */
    public function updateStatus(int $adminId, int $ticketId, string $status): array
    {
        $ticket = $this->repository->getTicketById($ticketId);
        if (!$ticket) {
            throw new ApiException('Support ticket not found.', 404);
        }

        $status = strtolower(trim($status));
        $allowed = ['open', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $allowed, true)) {
            throw new ApiException('Invalid status transition.', 422);
        }

        $this->repository->updateStatus($ticketId, $status);

        // Send customer notification when resolved or closed
        if (function_exists('send_notification') && !empty($ticket['customer_id']) && in_array($status, ['resolved', 'closed'], true)) {
            $statusLabel = ucfirst($status);
            send_notification(
                $this->repository->getDb(),
                $ticket['customer_id'],
                \App\Domain\Enums\NotificationTypeEnum::SYSTEM->value,
                "Ticket #{$ticket['ticket_no']} {$statusLabel}",
                "Your inquiry regarding '{$ticket['subject']}' has been marked as {$statusLabel}."
            );
        }

        return [
            'success' => true,
            'message' => "Ticket status updated to " . ucfirst(str_replace('_', ' ', $status)) . "."
        ];
    }
}
