<?php

namespace App\Presentation\Api\V1\Admin;

use App\Application\Services\AdminSupportService;
use App\Shared\Core\Exceptions\ApiException;
use App\Shared\Security\Auth;

class AdminSupportController
{
    private AdminSupportService $service;

    public function __construct(AdminSupportService $service)
    {
        $this->service = $service;
    }

    private function getAdminId(): int
    {
        $admin = Auth::user();
        $adminId = (int)($admin['id'] ?? ($_SESSION['admin_id'] ?? 0));
        if (!$adminId) {
            throw new ApiException('Unauthorized administrator session.', 401);
        }
        return $adminId;
    }

    /**
     * List support tickets with filters and queue stats.
     */
    public function index(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $this->getAdminId();

        $status = $_GET['status'] ?? null;
        $priority = $_GET['priority'] ?? null;
        $search = $_GET['search'] ?? null;

        return $this->service->listTickets($status, $priority, $search);
    }

    /**
     * Get single ticket detail with messages.
     */
    public function show(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $this->getAdminId();

        $ticketId = (int)($id ?? $_GET['id'] ?? 0);
        if (!$ticketId) {
            throw new ApiException('Ticket ID is required.', 422);
        }

        return $this->service->getTicketDetail($ticketId);
    }

    /**
     * Post official reply.
     */
    public function reply(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $adminId = $this->getAdminId();

        $ticketId = (int)($id ?? $_POST['ticket_id'] ?? 0);
        if (!$ticketId) {
            throw new ApiException('Ticket ID is required.', 422);
        }

        $message = (string)($_POST['message'] ?? '');
        $status = !empty($_POST['status']) ? (string)$_POST['status'] : null;

        return $this->service->replyTicket($adminId, $ticketId, $message, $status);
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(?string $id = null): array
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PATCH'], true)) {
            throw new ApiException('Method Not Allowed', 405);
        }
        $adminId = $this->getAdminId();

        $ticketId = (int)($id ?? $_POST['ticket_id'] ?? 0);
        if (!$ticketId) {
            throw new ApiException('Ticket ID is required.', 422);
        }

        $status = (string)($_POST['status'] ?? '');
        if (empty($status)) {
            throw new ApiException('Status is required.', 422);
        }

        return $this->service->updateStatus($adminId, $ticketId, $status);
    }
}
