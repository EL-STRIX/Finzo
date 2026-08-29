<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\Repositories\LedgerRepositoryInterface;
use App\Shared\Core\Database;

class LedgerRepository extends BaseRepository implements LedgerRepositoryInterface
{

    /**
     * Chart of Accounts (schema-aligned).
     * chart_of_accounts: account_code, name, category, normal_balance, status.
     * Balance is derived from posted journal lines according to the account's
     * normal balance (assets/expenses are debit-normal, others credit-normal).
     * Aliases (`code`, `type`) match the admin ledger UI contract.
     */
    public function getChartOfAccounts(): array
    {
        $stmt = $this->db->prepare("
            SELECT t.id, t.account_code AS code, t.name, t.category, t.normal_balance, t.status, t.created_at,
                   CASE WHEN t.normal_balance = 'credit'
                        THEN t.credits - t.debits
                        ELSE t.debits - t.credits
                   END AS balance
            FROM (
                SELECT c.id, c.account_code, c.name, c.category, c.normal_balance, c.status, c.created_at,
                       COALESCE(SUM(l.debit_amount), 0) AS debits,
                       COALESCE(SUM(l.credit_amount), 0) AS credits
                FROM chart_of_accounts c
                LEFT JOIN journal_lines l ON l.account_id = c.id
                GROUP BY c.id
            ) t
            ORDER BY t.account_code ASC
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll();

        // Normalize category enum to the UI group labels (Asset/Liability/Equity/Revenue/Expense)
        $typeMap = [
            'asset'      => 'Asset',
            'liability'  => 'Liability',
            'equity'     => 'Equity',
            'income'     => 'Revenue',
            'expense'    => 'Expense',
        ];
        foreach ($rows as &$row) {
            $row['type'] = $typeMap[$row['category']] ?? ucfirst((string) $row['category']);
            $row['balance'] = (float) $row['balance'];
        }
        unset($row);

        return $rows;
    }

    /**
     * Journal line rows (schema-aligned), flat for the admin ledger table.
     * journal_entries: journal_number, posting_date, reference, description, created_at.
     */
    public function getJournalEntries(int $limit, int $offset): array
    {
        $stmt = $this->db->prepare("
            SELECT
                l.id AS line_id, l.account_id, l.debit_amount, l.credit_amount,
                j.id, j.journal_number AS transaction_id, j.posting_date AS entry_date,
                j.description, j.reference, j.created_at,
                c.account_code, c.name AS account_name
            FROM journal_entries j
            JOIN journal_lines l ON j.id = l.journal_entry_id
            JOIN chart_of_accounts c ON l.account_id = c.id
            ORDER BY j.posting_date DESC, j.id DESC, l.sequence_number ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getJournalEntriesCount(): int
    {
        // Count flat line rows so pagination matches the journal table rendering.
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM journal_lines");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Active accounting period (schema-aligned).
     * accounting_periods uses status ENUM('open','closed','locked') + closed_at.
     * `name` alias matches the admin ledger UI contract.
     */
    public function getActiveAccountingPeriod(): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, period_name, period_name AS name, start_date, end_date, status, closed_at, created_at
            FROM accounting_periods
            WHERE status = 'open'
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Close an open accounting period.
     * Note: the schema has no `closed_by` column, so the actor is recorded
     * through the audit log at the service layer instead.
     */
    public function closeAccountingPeriod(int $periodId, int $closedBy): bool
    {
        $stmt = $this->db->prepare("
            UPDATE accounting_periods
            SET status = 'closed', closed_at = NOW()
            WHERE id = ? AND status = 'open'
        ");
        return $stmt->execute([$periodId]);
    }
}
