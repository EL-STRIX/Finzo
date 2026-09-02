<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\Repositories\ReportRepositoryInterface;
use PDO;

class ReportRepository extends BaseRepository implements ReportRepositoryInterface
{
    public function getFinancialSummary(array $filters): array
    {
        $where_clauses = ["t.status = 'completed'"];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where_clauses[] = "DATE(t.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where_clauses[] = "DATE(t.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['branch_id'])) {
            $where_clauses[] = "(u_from.branch_id = ? OR u_to.branch_id = ?)";
            $params[] = (int)$filters['branch_id'];
            $params[] = (int)$filters['branch_id'];
        }

        $where_sql = implode(' AND ', $where_clauses);

        $sql = "
            SELECT 
                t.type,
                COUNT(*) as total_count,
                COALESCE(SUM(t.amount), 0) as total_amount
            FROM transactions t
            LEFT JOIN accounts a_from ON t.from_account_id = a_from.id
            LEFT JOIN users u_from ON a_from.user_id = u_from.customer_id
            LEFT JOIN accounts a_to ON t.to_account_id = a_to.id
            LEFT JOIN users u_to ON a_to.user_id = u_to.customer_id
            WHERE {$where_sql}
            GROUP BY t.type
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
            'total_transactions' => 0,
            'net_cash_flow' => 0.0
        ];

        foreach ($results as $row) {
            $amount = (float)$row['total_amount'];
            $count = (int)$row['total_count'];
            $summary['total_transactions'] += $count;

            switch ($row['type']) {
                case 'deposit':
                    $summary['total_deposits'] = $amount;
                    $summary['deposit_count'] = $count;
                    break;
                case 'withdrawal':
                    $summary['total_withdrawals'] = $amount;
                    $summary['withdrawal_count'] = $count;
                    break;
                case 'transfer':
                    $summary['total_transfers'] = $amount;
                    $summary['transfer_count'] = $count;
                    break;
                case 'interest_credit':
                    $summary['total_interest_paid'] = $amount;
                    $summary['interest_count'] = $count;
                    break;
            }
        }

        $summary['net_cash_flow'] = $summary['total_deposits'] - $summary['total_withdrawals'];

        return $summary;
    }

    public function getUserDemographics(array $filters): array
    {
        // 1. User Status Distribution
        $userWhere = ["u.is_deleted = 0"];
        $userParams = [];

        if (!empty($filters['date_from'])) {
            $userWhere[] = "DATE(u.created_at) >= ?";
            $userParams[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $userWhere[] = "DATE(u.created_at) <= ?";
            $userParams[] = $filters['date_to'];
        }
        if (!empty($filters['status'])) {
            $userWhere[] = "u.status = ?";
            $userParams[] = $filters['status'];
        }

        $userWhereSql = implode(' AND ', $userWhere);

        $sql = "
            SELECT 
                u.status,
                COUNT(*) as count
            FROM users u
            WHERE {$userWhereSql}
            GROUP BY u.status
        ";

        $statusData = $this->fetchAll($sql, $userParams);
        $statuses = ['active' => 0, 'suspended' => 0];
        foreach ($statusData as $row) {
            $statuses[$row['status']] = (int)$row['count'];
        }

        // 2. Account Types Distribution
        $accWhere = ["u.is_deleted = 0"];
        $accParams = [];

        if (!empty($filters['date_from'])) {
            $accWhere[] = "COALESCE(a.opened_date, DATE(a.created_at)) >= ?";
            $accParams[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $accWhere[] = "COALESCE(a.opened_date, DATE(a.created_at)) <= ?";
            $accParams[] = $filters['date_to'];
        }
        if (!empty($filters['status'])) {
            $accWhere[] = "u.status = ?";
            $accParams[] = $filters['status'];
        }

        $accWhereSql = implode(' AND ', $accWhere);

        $accountSql = "
            SELECT 
                a.account_type,
                COUNT(*) as count
            FROM accounts a
            JOIN users u ON a.user_id = u.customer_id
            WHERE {$accWhereSql}
            GROUP BY a.account_type
        ";

        $accountData = $this->fetchAll($accountSql, $accParams);
        $accounts = ['savings' => 0, 'current' => 0];
        foreach ($accountData as $row) {
            $accounts[$row['account_type']] = (int)$row['count'];
        }

        // 3. KYC Verification Breakdown
        $kycWhere = ["u.is_deleted = 0"];
        $kycParams = [];
        if (!empty($filters['date_from'])) {
            $kycWhere[] = "DATE(k.created_at) >= ?";
            $kycParams[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $kycWhere[] = "DATE(k.created_at) <= ?";
            $kycParams[] = $filters['date_to'];
        }
        $kycWhereSql = implode(' AND ', $kycWhere);

        $kycSql = "
            SELECT 
                COALESCE(k.status, 'unsubmitted') as status,
                COUNT(*) as count
            FROM users u
            LEFT JOIN kyc_applications k ON u.customer_id = k.user_id
            WHERE {$kycWhereSql}
            GROUP BY COALESCE(k.status, 'unsubmitted')
        ";

        $kycData = $this->fetchAll($kycSql, $kycParams);
        $kycStatuses = [
            'approved' => 0,
            'under_review' => 0,
            'submitted' => 0,
            'draft' => 0,
            'rejected' => 0,
            'unsubmitted' => 0
        ];
        foreach ($kycData as $row) {
            $kycStatuses[$row['status']] = (int)$row['count'];
        }

        // 4. Branch Distribution
        $branchSql = "
            SELECT 
                COALESCE(b.name, 'Unassigned') as branch_name,
                COUNT(u.id) as count
            FROM users u
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE {$userWhereSql}
            GROUP BY COALESCE(b.name, 'Unassigned')
            ORDER BY count DESC
            LIMIT 10
        ";
        $branchData = $this->fetchAll($branchSql, $userParams);
        $branchDistribution = [];
        foreach ($branchData as $row) {
            $branchDistribution[$row['branch_name']] = (int)$row['count'];
        }

        return [
            'user_statuses' => $statuses,
            'account_types' => $accounts,
            'kyc_statuses' => $kycStatuses,
            'branch_distribution' => $branchDistribution
        ];
    }

    public function getFinancialTransactionsDetailed(array $filters, int $limit = 1000): array
    {
        $where_clauses = ["t.status = 'completed'"];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where_clauses[] = "DATE(t.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where_clauses[] = "DATE(t.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $where_sql = implode(' AND ', $where_clauses);

        $sql = "
            SELECT 
                t.txn_id,
                t.type,
                t.amount,
                t.status,
                COALESCE(a_from.account_no, 'SYSTEM') as from_account,
                COALESCE(a_to.account_no, 'SYSTEM') as to_account,
                t.description,
                t.created_at
            FROM transactions t
            LEFT JOIN accounts a_from ON t.from_account_id = a_from.id
            LEFT JOIN accounts a_to ON t.to_account_id = a_to.id
            WHERE {$where_sql}
            ORDER BY t.created_at DESC
            LIMIT " . (int)$limit;

        return $this->fetchAll($sql, $params);
    }

    public function getUserDemographicsDetailed(array $filters, int $limit = 1000): array
    {
        $where_clauses = ["u.is_deleted = 0"];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where_clauses[] = "DATE(u.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where_clauses[] = "DATE(u.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['status'])) {
            $where_clauses[] = "u.status = ?";
            $params[] = $filters['status'];
        }

        $where_sql = implode(' AND ', $where_clauses);

        $sql = "
            SELECT 
                u.customer_id,
                u.full_name,
                u.email,
                u.phone,
                u.status,
                u.tier,
                COALESCE(a.account_no, 'N/A') as account_no,
                COALESCE(a.account_type, 'N/A') as account_type,
                COALESCE(a.balance, 0) as balance,
                COALESCE(k.status, 'unsubmitted') as kyc_status,
                COALESCE(b.name, 'Unassigned') as branch_name,
                u.created_at
            FROM users u
            LEFT JOIN accounts a ON u.customer_id = a.user_id
            LEFT JOIN kyc_applications k ON u.customer_id = k.user_id
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE {$where_sql}
            ORDER BY u.created_at DESC
            LIMIT " . (int)$limit;

        return $this->fetchAll($sql, $params);
    }

    public function getBranchesList(): array
    {
        return $this->fetchAll("SELECT id, branch_code, name, state FROM branches WHERE status = 'active' ORDER BY name ASC", []);
    }
}
