<?php

namespace App\Infrastructure\Repositories;

use PDO;

class StaffRepository extends BaseRepository implements \App\Domain\Contracts\Repositories\StaffRepositoryInterface
{
    public function findAccountByNo(string $accountNumber)
    {
        return $this->fetchOne("
            SELECT u.full_name, a.account_no, a.account_type, a.status, a.balance, a.daily_limit
            FROM accounts a
            JOIN users u ON a.user_id = u.customer_id
            WHERE a.account_no = ?
        ", [$accountNumber]);
    }

    public function getUsedDailyLimit(string $accountNumber)
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) FROM transactions 
            WHERE from_account_id = (SELECT id FROM accounts WHERE account_no = ?)
            AND DATE(created_at) = CURDATE()
            AND status != 'failed'
            AND description != 'Account Restore Charge'
        ");
        $stmt->execute([$accountNumber]);
        return (float)$stmt->fetchColumn();
    }

    public function findStaffById(int $staffId)
    {
        return $this->fetchOne("SELECT * FROM staff WHERE id = ?", [$staffId], \App\Domain\Models\Staff::class);
    }

    public function findStaffPasswordHash(int $staffId)
    {
        $row = $this->fetchOne("SELECT password_hash FROM staff WHERE id = ?", [$staffId], \App\Domain\Models\Staff::class);
        return $row ? $row['password_hash'] : null;
    }

    public function updateStaffPassword(int $staffId, string $new_hash): bool
    {
        return $this->execute("UPDATE staff SET password_hash = ?, updated_at = NOW() WHERE id = ?", [$new_hash, $staffId]);
    }

    public function getStaffShiftDeposits(int $staffId)
    {
        $deposit = \App\Domain\Enums\TransactionTypeEnum::DEPOSIT->value;
        $completed = \App\Domain\Enums\AccountStatusEnum::COMPLETED->value;
        return $this->fetchOne("
            SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
            FROM transactions
            WHERE processed_by_staff_id = ? AND type = '{$deposit}'
                AND status = '{$completed}' AND DATE(created_at) = CURDATE()
        ", [$staffId]);
    }

    public function getStaffShiftWithdrawals(int $staffId)
    {
        $withdrawal = \App\Domain\Enums\TransactionTypeEnum::WITHDRAWAL->value;
        $completed = \App\Domain\Enums\AccountStatusEnum::COMPLETED->value;
        return $this->fetchOne("
            SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
            FROM transactions
            WHERE processed_by_staff_id = ? AND type = '{$withdrawal}'
                AND status = '{$completed}' AND DATE(created_at) = CURDATE()
        ", [$staffId]);
    }

    public function getStaffTotalTransactionsToday(int $staffId)
    {
        $completed = \App\Domain\Enums\AccountStatusEnum::COMPLETED->value;
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM transactions
            WHERE processed_by_staff_id = ? AND status = '{$completed}'
                AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([$staffId]);
        return (int)$stmt->fetchColumn();
    }

    public function getStaffRecentActivity(int $staffId)
    {
        return $this->fetchAll("
            SELECT
                t.txn_id,
                t.type,
                t.amount,
                t.status,
                t.description,
                t.reference_note,
                t.created_at,
                COALESCE(ta.account_no, fa.account_no) as account_no,
                COALESCE(tu.full_name, fu.full_name) as customer_name
            FROM transactions t
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            LEFT JOIN users tu ON ta.user_id = tu.customer_id
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN users fu ON fa.user_id = fu.customer_id
            WHERE t.processed_by_staff_id = ?
            ORDER BY t.created_at DESC
            LIMIT 10
        ", [$staffId]);
    }

    public function getStaffInfoWithBranch(int $staffId)
    {
        return $this->fetchOne("
            SELECT s.staff_id, s.full_name, s.designation, s.profile_photo, b.name as branch_name
            FROM staff s
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE s.id = ?
        ", [$staffId], \App\Domain\Models\Staff::class);
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }

    public function findAccountForUpdate(string $accountNumber)
    {
        return $this->fetchOne("
            SELECT a.id, a.account_no, a.balance, a.status, a.daily_limit, u.id as owner_user_id, u.customer_id, u.full_name
            FROM accounts a
            JOIN users u ON a.user_id = u.customer_id
            WHERE a.account_no = ?
            FOR UPDATE
        ", [$accountNumber]);
    }

    public function creditAccount(int $account_id, float $amount)
    {
        return $this->execute("UPDATE accounts SET balance = balance + ?, updated_at = NOW() WHERE id = ?", [$amount, $account_id]);
    }

    public function debitAccount(int $account_id, float $amount)
    {
        return $this->execute("UPDATE accounts SET balance = balance - ?, updated_at = NOW() WHERE id = ?", [$amount, $account_id]);
    }

    public function recordTransaction(array $data)
    {
        return $this->execute("
            INSERT INTO transactions
                (txn_id, from_account_id, to_account_id, type, amount, balance_after, description, reference_note, status, processed_by_staff_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ", [
            $data['txn_id'],
            $data['from_account_id'],
            $data['to_account_id'],
            $data['type'],
            $data['amount'],
            $data['balance_after'],
            $data['description'],
            $data['reference_note'],
            $data['status'],
            $data['processed_by_staff_id']
        ]);
    }



    public function countTransactionsByStaff(int $staffId, array $where_clauses, array $params)
    {
        $where_sql = implode(' AND ', $where_clauses);
        $count_sql = "
            SELECT COUNT(*)
            FROM transactions t
            LEFT JOIN accounts a ON COALESCE(t.to_account_id, t.from_account_id) = a.id
            LEFT JOIN users u ON a.user_id = u.customer_id
            WHERE {$where_sql}
        ";
        $stmt = $this->db->prepare($count_sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getTransactionsByStaff(int $staffId, array $where_clauses, array $params, int $per_page, int $offset)
    {
        $where_sql = implode(' AND ', $where_clauses);
        $sql = "
            SELECT
                t.txn_id,
                t.type,
                t.amount,
                t.balance_after,
                t.description,
                t.reference_note,
                t.status,
                t.created_at,
                COALESCE(a.account_no, 'N/A') as account_no,
                COALESCE(u.full_name, 'N/A') as customer_name
            FROM transactions t
            LEFT JOIN accounts a ON COALESCE(t.to_account_id, t.from_account_id) = a.id
            LEFT JOIN users u ON a.user_id = u.customer_id
            WHERE {$where_sql}
            ORDER BY t.created_at DESC
            LIMIT {$per_page} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTransactionReceipt(string $transactionId)
    {
        return $this->fetchOne("
            SELECT
                t.txn_id,
                t.type,
                t.amount,
                t.balance_after,
                t.description,
                t.reference_note,
                t.status,
                t.created_at,
                t.processed_by_staff_id,
                fa.account_no as from_account_no,
                COALESCE(fu.full_name, '') as from_name,
                ta.account_no as to_account_no,
                COALESCE(tu.full_name, '') as to_name,
                s.staff_id as staff_display_id,
                s.full_name as staff_name,
                b.name as branch_name
            FROM transactions t
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN users fu ON fa.user_id = fu.customer_id
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            LEFT JOIN users tu ON ta.user_id = tu.customer_id
            LEFT JOIN staff s ON t.processed_by_staff_id = s.id
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE t.txn_id = ?
        ", [$transactionId]);
    }

    public function getFullStaffProfile(int $staffId)
    {
        return $this->fetchOne("
            SELECT
                s.id, s.full_name, s.date_of_birth, s.gender, s.profile_photo,
                s.personal_email, s.phone, s.emergency_contact_name,
                s.emergency_contact_phone, s.staff_id, s.designation,
                s.department, s.branch_id, s.date_of_joining, s.status,
                s.created_by, s.created_at, s.updated_at,
                s.last_login_at, s.last_login_ip,
                s.failed_attempts, s.locked_until,
                s.street, s.city, s.state, s.pincode, s.country,
                s.id_type, s.id_number, s.front_image, s.back_image,
                b.name   AS branch_name,
                b.branch_code AS branch_code
            FROM staff s
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE s.id = ?
        ", [$staffId], \App\Domain\Models\Staff::class);
    }

    public function updateStaffProfile(int $staffId, array $fields, array $params)
    {
        $fields[] = 'updated_at = NOW()';
        $params[] = $staffId;
        $sql = "UPDATE staff SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->execute($sql, $params);
    }

    public function clockIn(int $staffId, string $staffIdentifier): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO staff_shifts (staff_id, shift_date, clock_in, status)
            VALUES (?, CURDATE(), NOW(), 'active')
            ON DUPLICATE KEY UPDATE clock_in = IF(clock_in IS NULL, NOW(), clock_in)
        ");
        return $stmt->execute([$staffIdentifier]);
    }

    public function clockOut(int $staffId, string $staffIdentifier, string $notes): bool
    {
        $stmt = $this->db->prepare("
            UPDATE staff_shifts 
            SET clock_out = NOW(), status = 'completed', notes = ?
            WHERE staff_id = ? AND shift_date = CURDATE() AND status = 'active'
        ");
        return $stmt->execute([$notes, $staffIdentifier]);
    }

    public function getCurrentShift(string $staffIdentifier): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, staff_id, shift_date, clock_in, clock_out, status, notes
            FROM staff_shifts
            WHERE staff_id = ? AND shift_date = CURDATE()
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$staffIdentifier]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
