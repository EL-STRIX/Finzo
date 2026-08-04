<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\Repositories\ReportRepositoryInterface;
use App\Shared\Core\Database;

class ReportRepository extends BaseRepository implements ReportRepositoryInterface
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function fetchOne(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getFinancialSummary(array $filters): array
    {
        $where_clauses = ["status = 'completed'"];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where_clauses[] = "DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where_clauses[] = "DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        // Branch filtering might require joining with users -> staff -> branch, or accounts -> branch.
        // Assuming transactions don't have direct branch_id. If needed, we'll keep it simple for now.

        $where_sql = implode(' AND ', $where_clauses);

        $sql = "
            SELECT 
                type,
                COUNT(*) as total_count,
                SUM(amount) as total_amount
            FROM transactions
            WHERE {$where_sql}
            GROUP BY type
        ";

        $results = $this->fetchAll($sql, $params);

        $summary = [
            'total_deposits' => 0.0,
            'total_withdrawals' => 0.0,
            'total_transfers' => 0.0,
            'total_interest_paid' => 0.0,
            'deposit_count' => 0,
            'withdrawal_count' => 0,
            'transfer_count' => 0,
            'interest_count' => 0,
        ];

        foreach ($results as $row) {
            switch ($row['type']) {
                case 'deposit':
                    $summary['total_deposits'] = (float)$row['total_amount'];
                    $summary['deposit_count'] = (int)$row['total_count'];
                    break;
                case 'withdrawal':
                    $summary['total_withdrawals'] = (float)$row['total_amount'];
                    $summary['withdrawal_count'] = (int)$row['total_count'];
                    break;
                case 'transfer':
                    $summary['total_transfers'] = (float)$row['total_amount'];
                    $summary['transfer_count'] = (int)$row['total_count'];
                    break;
                case 'interest_credit':
                    $summary['total_interest_paid'] = (float)$row['total_amount'];
                    $summary['interest_count'] = (int)$row['total_count'];
                    break;
            }
        }

        return $summary;
    }

    public function getUserDemographics(array $filters): array
    {
        $where_clauses = ["is_deleted = 0"];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where_clauses[] = "DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where_clauses[] = "DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $where_sql = implode(' AND ', $where_clauses);

        $sql = "
            SELECT 
                status,
                COUNT(*) as count
            FROM users
            WHERE {$where_sql}
            GROUP BY status
        ";

        $statusData = $this->fetchAll($sql, $params);
        $statuses = [];
        foreach ($statusData as $row) {
            $statuses[$row['status']] = (int)$row['count'];
        }

        $accountSql = "
            SELECT 
                account_type,
                COUNT(*) as count
            FROM accounts a
            JOIN users u ON a.user_id = u.customer_id
            WHERE u.is_deleted = 0
        ";
        if (!empty($filters['date_from'])) {
            $accountSql .= " AND DATE(a.opened_at) >= ?";
        }
        if (!empty($filters['date_to'])) {
            $accountSql .= " AND DATE(a.opened_at) <= ?";
        }
        $accountSql .= " GROUP BY account_type";

        $accountData = $this->fetchAll($accountSql, $params);
        $accounts = [];
        foreach ($accountData as $row) {
            $accounts[$row['account_type']] = (int)$row['count'];
        }

        return [
            'user_statuses' => $statuses,
            'account_types' => $accounts
        ];
    }
}
