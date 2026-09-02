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
    /**
     * Journal line rows (schema-aligned), flat for the admin ledger table.
     * journal_entries: journal_number, posting_date, reference, description, created_at.
     */
    public function getJournalEntries(int $limit, int $offset, string $search = '', string $type = ''): array
    {
        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(j.journal_number LIKE ? OR j.reference LIKE ? OR j.description LIKE ? OR c.account_code LIKE ? OR c.name LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if ($type === 'debit') {
            $where[] = "l.debit_amount > 0";
        } elseif ($type === 'credit') {
            $where[] = "l.credit_amount > 0";
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT
                l.id AS line_id, l.account_id, l.debit_amount, l.credit_amount,
                j.id, j.journal_number AS transaction_id, j.posting_date AS entry_date,
                j.description, j.reference, j.created_at,
                c.account_code, c.name AS account_name
            FROM journal_entries j
            JOIN journal_lines l ON j.id = l.journal_entry_id
            JOIN chart_of_accounts c ON l.account_id = c.id
            {$whereSql}
            ORDER BY j.posting_date DESC, j.id DESC, l.sequence_number ASC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->db->prepare($sql);
        $idx = 1;
        foreach ($params as $p) {
            $stmt->bindValue($idx++, $p, \PDO::PARAM_STR);
        }
        $stmt->bindValue($idx++, $limit, \PDO::PARAM_INT);
        $stmt->bindValue($idx++, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getJournalEntriesCount(string $search = '', string $type = ''): int
    {
        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(j.journal_number LIKE ? OR j.reference LIKE ? OR j.description LIKE ? OR c.account_code LIKE ? OR c.name LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if ($type === 'debit') {
            $where[] = "l.debit_amount > 0";
        } elseif ($type === 'credit') {
            $where[] = "l.credit_amount > 0";
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT COUNT(*)
            FROM journal_entries j
            JOIN journal_lines l ON j.id = l.journal_entry_id
            JOIN chart_of_accounts c ON l.account_id = c.id
            {$whereSql}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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

    /**
     * Open a new accounting period.
     */
    public function openAccountingPeriod(string $name, string $startDate, string $endDate): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO accounting_periods (period_name, start_date, end_date, status, created_at)
            VALUES (?, ?, ?, 'open', NOW())
        ");
        $stmt->execute([$name, $startDate, $endDate]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Real-time general ledger financial summary and double-entry balance check.
     */
    public function getLedgerSummary(): array
    {
        $totalsStmt = $this->db->query("
            SELECT
                COALESCE(SUM(debit_amount), 0) AS total_debits,
                COALESCE(SUM(credit_amount), 0) AS total_credits,
                COUNT(*) AS total_lines
            FROM journal_lines
        ");
        $totals = $totalsStmt->fetch() ?: ['total_debits' => 0, 'total_credits' => 0, 'total_lines' => 0];

        $totalDebits = (float)$totals['total_debits'];
        $totalCredits = (float)$totals['total_credits'];
        $diff = round(abs($totalDebits - $totalCredits), 2);
        $isBalanced = ($diff === 0.0);

        $journalsCount = (int)$this->db->query("SELECT COUNT(*) FROM journal_entries")->fetchColumn();

        $catStmt = $this->db->query("
            SELECT
                c.category,
                c.normal_balance,
                COALESCE(SUM(l.debit_amount), 0) AS debits,
                COALESCE(SUM(l.credit_amount), 0) AS credits
            FROM chart_of_accounts c
            LEFT JOIN journal_lines l ON l.account_id = c.id
            GROUP BY c.category, c.normal_balance
        ");
        $categories = $catStmt->fetchAll() ?: [];

        $balances = [
            'assets'      => 0.0,
            'liabilities' => 0.0,
            'equity'      => 0.0,
            'revenue'     => 0.0,
            'expense'     => 0.0,
        ];

        foreach ($categories as $cat) {
            $debits = (float)$cat['debits'];
            $credits = (float)$cat['credits'];
            $bal = ($cat['normal_balance'] === 'credit') ? ($credits - $debits) : ($debits - $credits);

            $c = strtolower($cat['category']);
            if ($c === 'asset') $balances['assets'] += $bal;
            elseif ($c === 'liability') $balances['liabilities'] += $bal;
            elseif ($c === 'equity') $balances['equity'] += $bal;
            elseif ($c === 'income') $balances['revenue'] += $bal;
            elseif ($c === 'expense') $balances['expense'] += $bal;
        }

        $accountsCount = (int)$this->db->query("SELECT COUNT(*) FROM chart_of_accounts")->fetchColumn();

        return [
            'total_debits'    => $totalDebits,
            'total_credits'   => $totalCredits,
            'is_balanced'     => $isBalanced,
            'difference'      => $diff,
            'assets'          => $balances['assets'],
            'liabilities'     => $balances['liabilities'],
            'equity'          => $balances['equity'],
            'revenue'         => $balances['revenue'],
            'expense'         => $balances['expense'],
            'total_accounts'  => $accountsCount,
            'total_journals'  => $journalsCount,
            'total_lines'     => (int)$totals['total_lines'],
        ];
    }

    /**
     * Retrieve full journal entry header and all associated debit/credit lines.
     */
    public function getJournalDetail(int $journalId): ?array
    {
        $headerStmt = $this->db->prepare("
            SELECT j.*, p.period_name
            FROM journal_entries j
            LEFT JOIN accounting_periods p ON j.period_id = p.id
            WHERE j.id = ?
        ");
        $headerStmt->execute([$journalId]);
        $header = $headerStmt->fetch();
        if (!$header) return null;

        $linesStmt = $this->db->prepare("
            SELECT l.*, c.account_code, c.name AS account_name, c.category
            FROM journal_lines l
            JOIN chart_of_accounts c ON l.account_id = c.id
            WHERE l.journal_entry_id = ?
            ORDER BY l.sequence_number ASC, l.id ASC
        ");
        $linesStmt->execute([$journalId]);
        $lines = $linesStmt->fetchAll();

        $header['lines'] = $lines;
        return $header;
    }

    /**
     * Retrieve all journal lines matching filter criteria for CSV export.
     */
    public function getAllJournalLinesForExport(string $search = '', string $type = ''): array
    {
        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(j.journal_number LIKE ? OR j.reference LIKE ? OR j.description LIKE ? OR c.account_code LIKE ? OR c.name LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if ($type === 'debit') {
            $where[] = "l.debit_amount > 0";
        } elseif ($type === 'credit') {
            $where[] = "l.credit_amount > 0";
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT
                j.journal_number, j.posting_date, j.reference, j.description, j.source_module,
                c.account_code, c.name AS account_name, c.category,
                CASE WHEN l.debit_amount > 0 THEN 'Debit' ELSE 'Credit' END AS entry_type,
                l.debit_amount, l.credit_amount, l.created_at
            FROM journal_entries j
            JOIN journal_lines l ON j.id = l.journal_entry_id
            JOIN chart_of_accounts c ON l.account_id = c.id
            {$whereSql}
            ORDER BY j.posting_date DESC, j.id DESC, l.sequence_number ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
