<?php
namespace App\Infrastructure\Repositories;

class PdfRepository extends BaseRepository implements \App\Domain\Contracts\Repositories\PdfRepositoryInterface
{
    public function getTransactionForReceipt(string $txnId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                t.txn_id, t.type, t.amount, t.balance_after, t.description,
                t.reference_note, t.status, t.created_at, t.from_account_id, t.to_account_id,
                fa.account_no as from_account_no,
                COALESCE(fu.full_name, 'N/A') as from_name,
                ta.account_no as to_account_no,
                COALESCE(tu.full_name, 'N/A') as to_name,
                COALESCE(s.staff_id, '') as staff_id,
                COALESCE(s.full_name, '') as staff_name,
                COALESCE(b.name, '') as branch_name
            FROM transactions t
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN users fu ON fa.user_id = fu.customer_id
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            LEFT JOIN users tu ON ta.user_id = tu.customer_id
            LEFT JOIN staff s ON t.processed_by_staff_id = s.id
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE t.txn_id = ?
        ");
        $stmt->execute([$txnId]);
        $txn = $stmt->fetch();
        return $txn ?: null;
    }

    public function getUserAccounts(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT a.id FROM accounts a JOIN users u ON a.user_id = u.customer_id WHERE u.id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(
PTO::FETCH_COLUMN);
    }
    
    public function getStaffStatementData(int $staffId): array
    {
        $stmt = $this->pdo->prepare("SELECT staff_id as staff_code, full_name, personal_email as email, phone, created_at FROM staff WHERE id = ?");
        $stmt->execute([$staffId]);
        $staff = $stmt->fetch();
        if (!$staff) return [];

        $txn_stmt = $this->pdo->prepare("
            SELECT
                t.txn_id, t.type, t.amount, t.created_at, t.status, t.description, t.reference_note,
                fa.account_no as from_acc, ta.account_no as to_acc,
                COALESCE(fu.full_name, 'N/A') as from_name,
                COALESCE(tu.full_name, 'N/A') as to_name
            FROM transactions t
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN users fu ON fa.user_id = fu.customer_id
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            LEFT JOIN users tu ON ta.user_id = tu.customer_id
            WHERE t.processed_by_staff_id = ?
            ORDER BY t.created_at DESC
            LIMIT 200
        ");
        $txn_stmt->execute([$staffId]);
        $transactions = $txn_stmt->fetchAll();

        return ['staff' => $staff, 'transactions' => $transactions];
    }
    
    public function getStatementData(int $userId, string $fromDate, string $toDate): array
    {
        $stmt = $this->pdo->prepare("
            SELECT a.id, a.account_no, a.account_type, a.balance, u.full_name, u.email, u.phone
            FROM accounts a JOIN users u ON a.user_id = u.customer_id
            WHERE u.id = ? AND a.status = 'active' LIMIT 1
        ");
        $stmt->execute([$userId]);
        $account = $stmt->fetch();
        if (!$account) return [];

        $stmt = $this->pdo->prepare("
            SELECT t.txn_id, t.type, t.amount, t.balance_after, t.description, t.created_at,
                   t.from_account_id, t.to_account_id
            FROM transactions t
            WHERE (t.from_account_id = ? OR t.to_account_id = ?)
                AND t.status = 'completed'
                AND DATE(t.created_at) BETWEEN ? AND ?
            ORDER BY t.created_at ASC
        ");
        $stmt->execute([$account['id'], $account['id'], $fromDate, $toDate]);
        $transactions = $stmt->fetchAll();
        return ['account' => $account, 'transactions' => $transactions];
    }
}
