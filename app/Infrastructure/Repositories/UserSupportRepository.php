<?php

namespace App\Infrastructure\Repositories;

class UserSupportRepository extends BaseRepository
{
    public function getTickets(int $userId): array
    {
        return $this->fetchAll("
            SELECT * FROM support_tickets WHERE user_id = ? ORDER BY updated_at DESC
        ", [$userId]);
    }

    public function getTicketById(int $ticketId, int $userId)
    {
        return $this->fetchOne("
            SELECT * FROM support_tickets WHERE id = ? AND user_id = ?
        ", [$ticketId, $userId]);
    }

    public function createTicket(int $userId, string $ticketNo, string $category, string $subject, string $priority): int
    {
        $this->execute("
            INSERT INTO support_tickets (ticket_no, user_id, category, subject, priority, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 'open', NOW(), NOW())
        ", [$ticketNo, $userId, $category, $subject, $priority]);
        return (int)$this->db->lastInsertId();
    }

    public function addMessage(int $ticketId, string $senderType, int $senderId, string $message): int
    {
        $this->execute("
            INSERT INTO support_ticket_messages (ticket_id, sender_type, sender_id, message, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ", [$ticketId, $senderType, $senderId, $message]);
        
        $this->execute("UPDATE support_tickets SET updated_at = NOW() WHERE id = ?", [$ticketId]);
        return (int)$this->db->lastInsertId();
    }

    public function getMessages(int $ticketId): array
    {
        return $this->fetchAll("
            SELECT * FROM support_ticket_messages WHERE ticket_id = ? ORDER BY created_at ASC
        ", [$ticketId]);
    }
}
