<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\Repositories\LedgerRepositoryInterface;
use App\Shared\Core\Database;

class LedgerRepository extends BaseRepository implements LedgerRepositoryInterface
{

    public function getChartOfAccounts(): array
    {
        $stmt = $this->db->prepare("
            SELECT id, account_code, account_name, account_type, balance, status, created_at
            FROM chart_of_accounts
            ORDER BY account_code ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getJournalEntries(int $limit, int $offset): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                j.id, j.transaction_id, j.entry_date, j.description, j.reference_id, j.created_at,
                l.id as line_id, l.account_id, l.debit_amount, l.credit_amount,
                c.account_code, c.account_name
            FROM journal_entries j
            JOIN journal_lines l ON j.id = l.journal_entry_id
            JOIN chart_of_accounts c ON l.account_id = c.id
            ORDER BY j.entry_date DESC, j.id DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getJournalEntriesCount(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM journal_entries");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getActiveAccountingPeriod(): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, period_name, start_date, end_date, is_closed
            FROM accounting_periods
            WHERE is_closed = 0
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function closeAccountingPeriod(int $periodId, int $closedBy): bool
    {
        $stmt = $this->db->prepare("
            UPDATE accounting_periods 
            SET is_closed = 1, closed_by = ?, closed_at = NOW()
            WHERE id = ? AND is_closed = 0
        ");
        return $stmt->execute([$closedBy, $periodId]);
    }
}
