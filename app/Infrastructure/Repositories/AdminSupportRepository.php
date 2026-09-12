<?php

namespace App\Infrastructure\Repositories;

class AdminSupportRepository extends BaseRepository
{
    /**
     * Fetch all support tickets with customer metadata and filters.
     */
    public function getTickets(?string $status = null, ?string $priority = null, ?string $search = null): array
    {
        $sql = "
            SELECT t.id, t.ticket_no, t.user_id, t.category, t.subject, t.priority, t.status, t.created_at, t.updated_at,
                   u.full_name AS customer_name, u.customer_id, u.email AS customer_email, u.phone AS customer_phone
            FROM support_tickets t
            JOIN users u ON t.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($status) && $status !== 'all') {
            $sql .= " AND t.status = ?";
            $params[] = strtolower($status);
        }

        if (!empty($priority) && $priority !== 'all') {
            $sql .= " AND t.priority = ?";
            $params[] = strtolower($priority);
        }

        if (!empty($search)) {
            $sql .= " AND (t.ticket_no LIKE ? OR u.full_name LIKE ? OR u.customer_id LIKE ? OR t.subject LIKE ?)";
            $term = '%' . trim($search) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY t.updated_at DESC";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Get single ticket by ID with customer metadata.
     */
    public function getTicketById(int $ticketId): ?array
    {
        $sql = "
            SELECT t.id, t.ticket_no, t.user_id, t.category, t.subject, t.priority, t.status, t.created_at, t.updated_at,
                   u.full_name AS customer_name, u.customer_id, u.email AS customer_email, u.phone AS customer_phone
            FROM support_tickets t
            JOIN users u ON t.user_id = u.id
            WHERE t.id = ?
            LIMIT 1
        ";
        $res = $this->fetchOne($sql, [$ticketId]);
        return $res ?: null;
    }

    /**
     * Get message thread for ticket.
     */
    public function getMessages(int $ticketId): array
    {
        $sql = "
            SELECT m.id, m.ticket_id, m.sender_type, m.sender_id, m.message, m.created_at,
                   CASE 
                       WHEN m.sender_type = 'user' THEN u.full_name
                       WHEN m.sender_type = 'admin' THEN COALESCE(a.full_name, 'Admin Support')
                       WHEN m.sender_type = 'staff' THEN COALESCE(s.full_name, 'Staff Support')
                       ELSE 'Support Officer'
                   END AS sender_name
            FROM support_ticket_messages m
            LEFT JOIN users u ON m.sender_id = u.id AND m.sender_type = 'user'
            LEFT JOIN admin_users a ON m.sender_id = a.id AND m.sender_type = 'admin'
            LEFT JOIN staff s ON m.sender_id = s.id AND m.sender_type = 'staff'
            WHERE m.ticket_id = ?
            ORDER BY m.created_at ASC
        ";
        return $this->fetchAll($sql, [$ticketId]);
    }

    /**
     * Add message to conversation.
     */
    public function addMessage(int $ticketId, string $senderType, int $senderId, string $message): int
    {
        $this->execute("
            INSERT INTO support_ticket_messages (ticket_id, sender_type, sender_id, message, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ", [$ticketId, $senderType, $senderId, $message]);

        $this->execute("UPDATE support_tickets SET updated_at = NOW() WHERE id = ?", [$ticketId]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(int $ticketId, string $status): bool
    {
        return $this->execute("
            UPDATE support_tickets SET status = ?, updated_at = NOW() WHERE id = ?
        ", [$status, $ticketId]);
    }

    /**
     * Update ticket priority.
     */
    public function updatePriority(int $ticketId, string $priority): bool
    {
        return $this->execute("
            UPDATE support_tickets SET priority = ?, updated_at = NOW() WHERE id = ?
        ", [$priority, $ticketId]);
    }

    /**
     * Get aggregate ticket counts for status filters.
     */
    public function getStats(): array
    {
        $row = $this->fetchOne("
            SELECT 
                COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END), 0) AS open_count,
                COALESCE(SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END), 0) AS in_progress_count,
                COALESCE(SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END), 0) AS resolved_count,
                COALESCE(SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END), 0) AS closed_count
            FROM support_tickets
        ");

        return [
            'total'             => (int)($row['total'] ?? 0),
            'open_count'        => (int)($row['open_count'] ?? 0),
            'in_progress_count' => (int)($row['in_progress_count'] ?? 0),
            'resolved_count'    => (int)($row['resolved_count'] ?? 0),
            'closed_count'      => (int)($row['closed_count'] ?? 0),
        ];
    }
}
