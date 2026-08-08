<?php

namespace App\Infrastructure\Repositories;

use PDO;
use PDOException;

class AdminOperationsRepository extends BaseRepository implements \App\Domain\Contracts\Repositories\AdminOperationsRepositoryInterface
{
    // ---------------------------------------------------------
    // Audit Log Methods
    // ---------------------------------------------------------
    public function countAuditLogs(array $filters): int
    {
        [$whereSql, $params] = $this->buildAuditLogFilters($filters);
        return (int)$this->fetchOne("SELECT COUNT(*) as count FROM audit_log al WHERE {$whereSql}", $params)['count'] ?? 0;
    }

    public function getAuditLogs(array $filters, int $perPage, int $offset) {
        [$whereSql, $params] = $this->buildAuditLogFilters($filters);
        return $this->fetchAll("
            SELECT al.id, al.action_type, al.entity_id, al.entity_type,
                   al.actor_id, al.actor_role, al.details, al.ip_address, al.created_at,
                   al.actor_id as actor_display_id,
                   al.actor_role as actor_specific_role,
                   COALESCE(
                       CASE WHEN al.entity_type = 'user' THEN u.customer_id ELSE NULL END,
                       CASE WHEN al.entity_type = 'staff' THEN s.staff_id ELSE NULL END,
                       CASE WHEN al.entity_type = 'admin' THEN a.admin_id ELSE NULL END,
                       al.entity_id
                   ) as entity_display_id
            FROM audit_log al
            LEFT JOIN users u ON al.entity_type = 'user' AND (u.id = al.entity_id OR u.customer_id = al.entity_id)
            LEFT JOIN staff s ON al.entity_type = 'staff' AND (s.id = al.entity_id OR s.staff_id = al.entity_id)
            LEFT JOIN admin_users a ON al.entity_type = 'admin' AND (a.id = al.entity_id OR a.admin_id = al.entity_id)
            WHERE {$whereSql}
            ORDER BY al.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", $params);
    }

    public function getAuditLogActionTypes() {
        $stmt = $this->db->query("SELECT DISTINCT action_type FROM audit_log ORDER BY action_type");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAuditLogStats(array $filters) {
        [$whereSql, $params] = $this->buildAuditLogFilters($filters);
        return $this->fetchOne("
            SELECT 
                SUM(CASE WHEN al.actor_role LIKE '%Admin%' OR al.actor_role = 'Moderator' OR al.actor_role IN ('admin', 'super_admin', 'moderator') THEN 1 ELSE 0 END) as admin_count,
                SUM(CASE WHEN al.actor_role IN ('Staff', 'staff') THEN 1 ELSE 0 END) as staff_count,
                SUM(CASE WHEN al.actor_role IN ('User', 'user') THEN 1 ELSE 0 END) as user_count
            FROM audit_log al
            WHERE {$whereSql}
        ", $params);
    }

    private function buildAuditLogFilters(array $filters): array
    {
        $where_clauses = ["1=1"];
        $params = [];

        if (isset($filters['action_type']) && $filters['action_type'] !== 'all') {
            $where_clauses[] = "al.action_type LIKE ?";
            $params[] = "%{$filters['action_type']}%";
        }

        if (isset($filters['actor_role']) && $filters['actor_role'] !== 'all' && in_array($filters['actor_role'], ['admin', 'staff', 'system', 'user'])) {
            if ($filters['actor_role'] === 'admin') {
                $where_clauses[] = "(al.actor_role LIKE '%Admin%' OR al.actor_role = 'Moderator' OR al.actor_role IN ('admin', 'super_admin', 'moderator'))";
            } elseif ($filters['actor_role'] === 'staff') {
                $where_clauses[] = "al.actor_role IN ('Staff', 'staff')";
            } elseif ($filters['actor_role'] === 'system') {
                $where_clauses[] = "al.actor_role IN ('System', 'system')";
            } elseif ($filters['actor_role'] === 'user') {
                $where_clauses[] = "al.actor_role IN ('User', 'user')";
            }
        }

        if (!empty($filters['from_date'])) {
            $where_clauses[] = "DATE(al.created_at) >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where_clauses[] = "DATE(al.created_at) <= ?";
            $params[] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $where_clauses[] = "(al.actor_id LIKE ? OR al.entity_id LIKE ?)";
            $s = "%{$filters['search']}%";
            $params[] = $s;
            $params[] = $s;
        }

        return [implode(' AND ', $where_clauses), $params];
    }

    // ---------------------------------------------------------
    // Dashboard Methods
    // ---------------------------------------------------------
    public function getSingleStat(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getDashboardRecentUsers() {
        return $this->fetchAll("
            SELECT id, full_name, email, status, created_at, profile_picture
            FROM users
            WHERE is_deleted = 0
            ORDER BY created_at DESC
            LIMIT 10
        ", []);
    }

    public function getMonthlyChartDataRow(string $monthDate) {
        return $this->fetchOne("
            SELECT
                COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as deposits,
                COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as withdrawals,
                COALESCE(SUM(CASE WHEN type = 'transfer' THEN amount ELSE 0 END), 0) as transfers,
                COUNT(*) as count
            FROM transactions
            WHERE status = 'completed'
                AND DATE_FORMAT(created_at, '%Y-%m') = ?
        ", [$monthDate], \App\Domain\Models\Admin::class);
    }

    public function getAdminById(int $adminId) {
        return $this->fetchOne("SELECT full_name, email, profile_photo, admin_role, admin_id FROM admin_users WHERE id = ?", [$adminId], \App\Domain\Models\Admin::class) ?: null;
    }

    // ---------------------------------------------------------
    // Deleted Users Methods
    // ---------------------------------------------------------
    public function countDeletedUsers(string $whereSql, array $params): int
    {
        return (int)$this->fetchOne("
            SELECT COUNT(*) as count
            FROM users u 
            LEFT JOIN accounts a ON u.customer_id = a.user_id 
            WHERE {$whereSql}
        ", $params)['count'] ?? 0;
    }

    public function getDeletedUsers(string $whereSql, array $params, int $perPage, int $offset) {
        return $this->fetchAll("
            SELECT u.id, u.customer_id, u.full_name, u.email, u.phone, u.status, u.deleted_at, u.created_at, u.profile_picture,
                   a.account_no, a.account_type, a.balance
            FROM users u
            LEFT JOIN accounts a ON u.customer_id = a.user_id
            WHERE {$whereSql}
            ORDER BY u.deleted_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", $params);
    }

    // ---------------------------------------------------------
    // Interest Rate Methods
    // ---------------------------------------------------------
    public function getCurrentInterestRate() {
        return $this->fetchOne("
            SELECT ir.rate, ir.apply_from_type, ir.apply_from_date, ir.created_at,
                   au.full_name as changed_by_name
            FROM interest_rates ir
            JOIN admin_users au ON ir.changed_by_admin_id = au.admin_id
            ORDER BY ir.created_at DESC
            LIMIT 1
        ", []) ?: null;
    }

    public function getInterestHistory() {
        return $this->fetchAll("
            SELECT ir.id, ir.rate, ir.apply_from_type, ir.apply_from_date, ir.created_at,
                   au.full_name as changed_by_name
            FROM interest_rates ir
            JOIN admin_users au ON ir.changed_by_admin_id = au.admin_id
            ORDER BY ir.created_at DESC
            LIMIT 10
        ", []);
    }

    public function insertInterestRate(float $rate, string $applyFrom, string $applyDate, string $adminDisplayId): void
    {
        $this->execute("
            INSERT INTO interest_rates (rate, apply_from_type, apply_from_date, changed_by_admin_id, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ", [$rate, $applyFrom, $applyDate, $adminDisplayId]);
    }

    public function updateActiveSavingsInterestRate(float $rate): void
    {
        $this->execute("UPDATE accounts SET interest_rate = ?, updated_at = NOW() WHERE status = 'active' AND account_type = 'savings'", [$rate]);
    }

    public function getActiveSavingsAccounts() {
        return $this->fetchAll("SELECT id, user_id, balance FROM accounts WHERE account_type = 'savings' AND status = 'active' AND balance > 0", []);
    }

    public function addTransaction(string $txnId, ?int $fromAccountId, int $toAccountId, string $type, float $amount, float $balanceAfter, string $description, string $referenceNote, string $status): void
    {
        $this->execute("
            INSERT INTO transactions (txn_id, from_account_id, to_account_id, type, amount, balance_after, description, reference_note, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ", [$txnId, $fromAccountId, $toAccountId, $type, $amount, $balanceAfter, $description, $referenceNote, $status]);
    }

    public function updateAccountBalance(float $balance, int $accountId): void
    {
        $this->execute("UPDATE accounts SET balance = ?, updated_at = NOW() WHERE id = ?", [$balance, $accountId]);
    }

    public function addNotification(int $userId, string $type, string $title, string $message): void
    {
        $this->execute("
            INSERT INTO notifications (user_id, type, title, message, is_read, created_at)
            VALUES (?, ?, ?, ?, 0, NOW())
        ", [$userId, $type, $title, $message]);
    }

    public function getTransactionForReversal(string $txnId)
    {
        return $this->fetchOne("SELECT * FROM transactions WHERE txn_id = ? FOR UPDATE", [$txnId]);
    }

    public function getAccountForUpdate(int $accountId)
    {
        return $this->fetchOne("
            SELECT a.id, a.account_no, a.balance, u.full_name, u.customer_id
            FROM accounts a
            JOIN users u ON a.user_id = u.customer_id
            WHERE a.id = ? AND a.status = 'active'
            FOR UPDATE
        ", [$accountId]);
    }

    public function getExportUsers(string $whereSql, array $params) {
        return $this->fetchAll("
            SELECT
                u.customer_id, u.full_name, u.email, u.phone, u.status, u.created_at, u.last_login_at
            FROM users u
            WHERE {$whereSql}
            ORDER BY u.created_at DESC
        ", $params);
    }

    public function exportUsersGenerator(string $whereSql, array $params): \Generator {
        $sql = "
            SELECT
                u.customer_id as 'Customer ID',
                u.full_name as 'Full Name',
                u.email as 'Email',
                u.phone as 'Phone',
                u.status as 'Status',
                u.created_at as 'Created At',
                u.last_login_at as 'Last Login'
            FROM users u
            WHERE {$whereSql}
            ORDER BY u.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    // ---------------------------------------------------------
    // Locked Accounts Methods
    // ---------------------------------------------------------
    public function countLockedAccounts(string $whereSql, array $params): int
    {
        return (int)$this->fetchOne("SELECT COUNT(*) as count FROM users u WHERE {$whereSql}", $params)['count'] ?? 0;
    }

    public function getLockedAccounts(string $whereSql, array $params, int $perPage, int $offset) {
        return $this->fetchAll("
            SELECT u.id, u.full_name, u.email, u.phone, u.status, u.profile_picture,
                   u.failed_attempts, u.locked_until, u.last_login_at, u.last_login_ip,
                   u.updated_at, u.temp_pass_status,
                   a.account_no, a.account_type
            FROM users u
            LEFT JOIN accounts a ON u.customer_id = a.user_id
            WHERE {$whereSql}
            ORDER BY u.locked_until DESC, u.failed_attempts DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", $params);
    }

    public function getLockedAccountDetail(int $userId) {
        return $this->fetchOne("
            SELECT u.id, u.customer_id, u.full_name, u.email, u.phone, u.status,
                   u.profile_picture, u.failed_attempts, u.locked_until,
                   u.last_login_at, u.last_login_ip, u.updated_at,
                   a.account_no, a.account_type, a.balance, a.opened_date
            FROM users u
            LEFT JOIN accounts a ON u.customer_id = a.user_id
            WHERE u.id = ?
        ", [$userId], \App\Domain\Models\Admin::class) ?: null;
    }

    public function getUserAuditLogs(string $customerId) {
        return $this->fetchAll("
            SELECT action_type, entity_type, entity_id, ip_address, created_at, details
            FROM audit_log
            WHERE (entity_type = 'user' AND entity_id = ?)
               OR (actor_id = ? AND actor_role = 'User')
            ORDER BY created_at DESC
            LIMIT 20
        ", [$customerId, $customerId], \App\Domain\Models\Admin::class);
    }

    // ---------------------------------------------------------
    // Pending Approvals Methods
    // ---------------------------------------------------------
    public function countPendingApprovals(string $whereSql, array $params): int
    {
        return (int)$this->fetchOne("SELECT COUNT(*) as count FROM users u WHERE {$whereSql}", $params)['count'] ?? 0;
    }

    public function getPendingApprovals(string $whereSql, array $params, int $perPage, int $offset) {
        return $this->fetchAll("
            SELECT u.id, u.customer_id, u.full_name, u.email, u.phone, u.status,
                   u.kyc_submitted_at, u.created_at, u.profile_picture,
                   a.account_type,
                   k.application_no, k.submission_count, k.submitted_at,
                   SUBSTRING(u.full_name, 1, 1) as initials,
                   CASE u.status
                       WHEN 'pending'      THEN 'Pending Review'
                       WHEN 'under_review' THEN 'Under Review'
                       ELSE u.status
                   END as status_text,
                   k.id_type
            FROM users u
            LEFT JOIN accounts a ON u.customer_id = a.user_id
            LEFT JOIN kyc_applications k ON u.customer_id = k.user_id
            WHERE {$whereSql}
            ORDER BY u.kyc_submitted_at DESC, u.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", $params);
    }

    public function getPendingApprovalsStats() {
        return $this->fetchOne("
            SELECT
                SUM(CASE WHEN status = 'pending'      THEN 1 ELSE 0 END) as pending_review,
                SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) as under_review,
                SUM(CASE WHEN status = 'active' AND DATE(kyc_submitted_at) = CURDATE() THEN 1 ELSE 0 END) as approved_today,
                SUM(CASE WHEN status = 'rejected' AND DATE(kyc_submitted_at) = CURDATE() THEN 1 ELSE 0 END) as rejected_today,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as total_approved
            FROM users WHERE is_deleted = 0
        ", []);
    }

    // ---------------------------------------------------------
    // Recover User Methods
    // ---------------------------------------------------------
    public function getUserForRecovery(int $userId) {
        return $this->fetchOne("SELECT id, customer_id, full_name, is_deleted FROM users WHERE id = ?", [$userId], \App\Domain\Models\Admin::class) ?: null;
    }

    public function recoverUser(int $userId, string $customerId): void
    {
        $this->execute("UPDATE users SET is_deleted = 0, deleted_at = NULL, status = 'active', updated_at = NOW() WHERE id = ?", [$userId]);
        $this->execute("UPDATE accounts SET status = 'active', updated_at = NOW() WHERE user_id = ? AND status = 'closed'", [$customerId]);
    }

    // ---------------------------------------------------------
    // Review Application Methods
    // ---------------------------------------------------------
    public function getApplicationUser(int $userId) {
        return $this->fetchOne("
            SELECT id, customer_id, full_name, email, phone, status, profile_picture, dob, gender, father_name, mother_name, marital_status, nationality,
                   occupation, annual_income, source_of_income, street, city, state, pincode, country,
                   id_type, id_number, id_front_path, id_back_path,
                   kyc_submitted_at, rejection_reason, created_at
            FROM users WHERE id = ? AND is_deleted = 0
        ", [$userId], \App\Domain\Models\Admin::class) ?: null;
    }

    public function getAccountPreferences(string $customerId) {
        return $this->fetchOne("SELECT account_type, balance as initial_deposit, status FROM accounts WHERE user_id = ?", [$customerId], \App\Domain\Models\Admin::class) ?: null;
    }

    public function getApplicationAuditLogs(string $customerId) {
        return $this->fetchAll("
            SELECT details, created_at 
            FROM audit_log 
            WHERE action_type = 'kyc_rejected' AND entity_id = ? 
            ORDER BY id ASC
        ", [$customerId], \App\Domain\Models\Admin::class);
    }

    public function getAccountIdByCustomerId(string $customerId): ?int
    {
        return (int)($this->fetchOne("SELECT id FROM accounts WHERE user_id = ?", [$customerId], \App\Domain\Models\Admin::class)['id'] ?? 0) ?: null;
    }

    public function approveApplication(int $userId, string $customerId, string $accountNo, string $adminDisplayId): void
    {
        $this->execute("UPDATE users SET status = 'active', updated_at = NOW() WHERE id = ?", [$userId]);
        $this->execute("
            UPDATE accounts SET
                account_no = ?, status = 'active', opened_date = CURDATE(), updated_at = NOW()
            WHERE user_id = ?
        ", [$accountNo, $customerId]);
        $this->execute("
            UPDATE kyc_applications SET
                status = 'approved',
                reviewed_by = ?,
                reviewed_at = NOW()
            WHERE user_id = ?
        ", [$adminDisplayId, $customerId]);
    }

    public function rejectApplication(int $userId, string $customerId, string $reason, string $adminDisplayId): void
    {
        $this->execute("UPDATE users SET status = 'rejected', rejection_reason = ?, updated_at = NOW() WHERE id = ?", [$reason, $userId]);
        $this->execute("UPDATE accounts SET status = 'rejected', updated_at = NOW() WHERE user_id = ?", [$customerId]);
        $this->execute("
            UPDATE kyc_applications SET
                status = 'rejected',
                reviewed_by = ?,
                reviewed_at = NOW(),
                rejection_reason = ?
            WHERE user_id = ?
        ", [$adminDisplayId, $reason, $customerId]);
    }

    public function saveAdminNotes(string $customerId, string $notes): void
    {
        $this->execute("UPDATE kyc_applications SET admin_notes = ? WHERE user_id = ?", [$notes, $customerId]);
    }

    // ---------------------------------------------------------
    // Suspended Users Methods
    // ---------------------------------------------------------
    public function countSuspendedUsers(string $whereSql, array $params): int
    {
        $sql = "
            SELECT COUNT(DISTINCT u.id) as count
            FROM users u
            LEFT JOIN accounts a ON u.customer_id = a.user_id
            LEFT JOIN (
                SELECT log1.entity_id, log1.details, log1.created_at, log1.actor_id
                FROM audit_log log1
                INNER JOIN (
                    SELECT entity_id, MAX(id) as max_id
                    FROM audit_log
                    WHERE action_type = 'user_suspended' AND entity_type = 'user'
                    GROUP BY entity_id
                ) log2 ON log1.id = log2.max_id
            ) l ON u.customer_id = l.entity_id
            WHERE {$whereSql}
        ";
        return (int)$this->fetchOne($sql, $params)['count'] ?? 0;
    }

    public function getSuspendedUsers(string $whereSql, array $params, int $perPage, int $offset) {
        $sql = "
            SELECT u.id, u.customer_id, u.full_name, u.email, u.phone, u.status, u.created_at, u.updated_at,
                   a.account_no, a.account_type, a.balance,
                   COALESCE(l.details, 'Suspended') as raw_reason,
                   COALESCE(l.created_at, u.updated_at) as raw_suspended_at,
                   COALESCE(l.actor_id, 'Admin') as suspended_by
            FROM users u
            LEFT JOIN accounts a ON u.customer_id = a.user_id
            LEFT JOIN (
                SELECT log1.entity_id, log1.details, log1.created_at, log1.actor_id
                FROM audit_log log1
                INNER JOIN (
                    SELECT entity_id, MAX(id) as max_id
                    FROM audit_log
                    WHERE action_type = 'user_suspended' AND entity_type = 'user'
                    GROUP BY entity_id
                ) log2 ON log1.id = log2.max_id
            ) l ON u.customer_id = l.entity_id
            WHERE {$whereSql}
            ORDER BY raw_suspended_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        return $this->fetchAll($sql, $params);
    }

    // ---------------------------------------------------------
    // Transactions Methods
    // ---------------------------------------------------------
    public function countTransactions(string $baseSql, array $params): int
    {
        return (int)$this->fetchOne("SELECT COUNT(*) as count " . $baseSql, $params)['count'] ?? 0;
    }

    public function getTransactions(string $baseSql, array $params, int $perPage, int $offset) {
        $sql = "
            SELECT
                t.id, t.txn_id, t.type, t.amount, t.balance_after, t.description,
                t.reference_note, t.status, t.created_at,
                COALESCE(fa.account_no, 'N/A') as from_account_no,
                COALESCE(fu.full_name, 'N/A') as from_user_name,
                fu.profile_picture as from_user_avatar,
                COALESCE(ta.account_no, 'N/A') as to_account_no,
                COALESCE(tu.full_name, 'N/A') as to_user_name,
                tu.profile_picture as to_user_avatar,
                COALESCE(s.staff_id, '') as processed_by
            {$baseSql}
            ORDER BY t.created_at DESC, t.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        return $this->fetchAll($sql, $params);
    }

    public function getTransactionsSummary(string $baseSql, array $params) {
        $sql = "
            SELECT
                COUNT(*) as total_count,
                COALESCE(SUM(t.amount), 0) as total_amount,
                COALESCE(SUM(CASE WHEN t.type = 'deposit' THEN t.amount ELSE 0 END), 0) as total_deposits,
                COALESCE(SUM(CASE WHEN t.type = 'withdrawal' THEN t.amount ELSE 0 END), 0) as total_withdrawals,
                COALESCE(SUM(CASE WHEN t.type = 'transfer' THEN t.amount ELSE 0 END), 0) as total_transfers
            {$baseSql}
        ";
        return $this->fetchOne($sql, $params);
    }

    public function exportTransactions(string $baseSql, array $params) {
        $sql = "
            SELECT
                t.txn_id as 'Transaction ID',
                COALESCE(fa.account_no, 'N/A') as 'From Account',
                COALESCE(fu.full_name, 'N/A') as 'From Name',
                COALESCE(ta.account_no, 'N/A') as 'To Account',
                COALESCE(tu.full_name, 'N/A') as 'To Name',
                t.type as 'Type',
                t.amount as 'Amount',
                t.status as 'Status',
                t.description as 'Description',
                COALESCE(s.staff_id, 'N/A') as 'Processed By',
                t.created_at as 'Date'
            {$baseSql}
            ORDER BY t.created_at DESC, t.id DESC
        ";
        return $this->fetchAll($sql, $params);
    }

    public function exportTransactionsGenerator(string $baseSql, array $params): \Generator {
        $sql = "
            SELECT
                t.txn_id as 'Transaction ID',
                COALESCE(fa.account_no, 'N/A') as 'From Account',
                COALESCE(fu.full_name, 'N/A') as 'From Name',
                COALESCE(ta.account_no, 'N/A') as 'To Account',
                COALESCE(tu.full_name, 'N/A') as 'To Name',
                t.type as 'Type',
                t.amount as 'Amount',
                t.status as 'Status',
                t.description as 'Description',
                COALESCE(s.staff_id, 'N/A') as 'Processed By',
                t.created_at as 'Date'
            {$baseSql}
            ORDER BY t.created_at DESC, t.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    // ---------------------------------------------------------
    // Users Methods
    // ---------------------------------------------------------
    public function countUsers(string $whereSql, array $params): int
    {
        return (int)$this->fetchOne("SELECT COUNT(u.id) as count FROM users u WHERE {$whereSql}", $params)['count'] ?? 0;
    }

    public function getUsers(string $whereSql, array $params, int $perPage, int $offset) {
        $sql = "
            SELECT
                u.id, u.customer_id, u.full_name, u.email, u.phone, u.status, u.created_at, u.last_login_at, u.profile_picture
            FROM users u
            WHERE {$whereSql}
            ORDER BY u.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        return $this->fetchAll($sql, $params);
    }

    public function getUsersStats() {
        return $this->fetchOne("
            SELECT
                SUM(CASE WHEN is_deleted = 0 THEN 1 ELSE 0 END) as total_users,
                SUM(CASE WHEN is_deleted = 0 AND status = 'active' THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN is_deleted = 0 AND status = 'suspended' THEN 1 ELSE 0 END) as suspended_users,
                SUM(CASE WHEN is_deleted = 0 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as new_this_month,
                SUM(CASE WHEN is_deleted = 1 THEN 1 ELSE 0 END) as soft_deleted
            FROM users
        ", []);
    }

    // ---------------------------------------------------------
    // Suspended User Detail Methods
    // ---------------------------------------------------------
    public function getSuspendedUser(int $userId) {
        return $this->fetchOne("
            SELECT id, customer_id, full_name, email, phone, status, dob, gender, father_name,
                   occupation, annual_income, street, city, state, pincode, country,
                   id_type, id_number, kyc_submitted_at, created_at, updated_at
            FROM users WHERE id = ? AND status = 'suspended' AND is_deleted = 0
        ", [$userId], \App\Domain\Models\Admin::class);
    }

    public function getSuspendedUserProfile(int $userId) {
        return $this->getSuspendedUser($userId);
    }

    public function getSuspendedUserAccount(string $customerId)
    {
        $stmt = $this->db->prepare("
            SELECT account_no, account_type, balance, status, created_at as opened_at
            FROM accounts WHERE user_id = ?
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserSuspensionAudit(string $customerId)
    {
        $stmt = $this->db->prepare("
            SELECT details, created_at, actor_id
            FROM audit_log
            WHERE action_type = 'user_suspended' AND entity_id = ?
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecentTransactions(string $accountNo, int $limit)
    {
        $stmt = $this->db->prepare("
            SELECT t.txn_id, t.type, t.amount, t.description, t.status, t.created_at
            FROM transactions t
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            WHERE fa.account_no = ? OR ta.account_no = ?
            ORDER BY t.created_at DESC
            LIMIT ?
        ");
        // Bind parameters carefully for limit
        $stmt->bindValue(1, $accountNo);
        $stmt->bindValue(2, $accountNo);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserForUnlock(int $userId)
    {
        $stmt = $this->db->prepare("SELECT id, customer_id, full_name, email, failed_attempts FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function unlockUserAccount(string $passwordHash, int $userId)
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET locked_until = NULL, failed_attempts = 0, password_hash = ?, temp_pass_status = 'done', updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$passwordHash, $userId]);
    }

    public function getUserForAction(int $userId)
    {
        $stmt = $this->db->prepare("SELECT id, customer_id, full_name, email, status, is_deleted FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserForActionByCustomer(string $customerId)
    {
        $stmt = $this->db->prepare("SELECT id, balance FROM accounts WHERE user_id = ?");
        $stmt->execute([$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAdminPassword(int $adminId)
    {
        $stmt = $this->db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
        $stmt->execute([$adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function suspendUserAccount(int $userId, string $customerId)
    {
        $stmt = $this->db->prepare("UPDATE users SET status = 'suspended', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
        $stmt = $this->db->prepare("UPDATE accounts SET status = 'suspended', updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$customerId]);
    }

    public function reinstateUserAccount(int $userId, string $customerId)
    {
        $stmt = $this->db->prepare("UPDATE users SET status = 'active', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
        $stmt = $this->db->prepare("UPDATE accounts SET status = 'active', updated_at = NOW() WHERE user_id = ? AND status = 'suspended'");
        $stmt->execute([$customerId]);
    }

    public function softDeleteUserAccount(int $userId, string $customerId)
    {
        $stmt = $this->db->prepare("UPDATE users SET is_deleted = 1, deleted_at = NOW(), status = 'suspended', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
        $stmt = $this->db->prepare("UPDATE accounts SET status = 'closed', updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$customerId]);
    }

    public function restoreUserFromSoftDelete(int $userId)
    {
        $stmt = $this->db->prepare("UPDATE users SET is_deleted = 0, deleted_at = NULL, status = 'active', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }

    public function addSystemChargeTransaction(string $txnId, int $accountId, float $amount)
    {
        $stmt = $this->db->prepare("INSERT INTO transactions (txn_id, from_account_id, type, amount, balance_after, description, reference_note, status, created_at) VALUES (?, ?, 'withdrawal', ?, 500, 'Account Restore Charge', 'Account recovered from soft-delete', 'completed', NOW())");
        $stmt->execute([$txnId, $accountId, $amount]);
    }

    public function updateAccountBalanceLease500(string $customerId)
    {
        $stmt = $this->db->prepare("UPDATE accounts SET status = 'active', balance = LEAST(balance, 500), updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$customerId]);
    }

    public function activateAccount(string $customerId)
    {
        $stmt = $this->db->prepare("UPDATE accounts SET status = 'active', updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$customerId]);
    }

    public function hardDeleteUserAccount(int $userId, string $customerId)
    {
        throw new \App\Shared\Core\Exceptions\BusinessRuleException("Hard deletion of users and accounts is strictly prohibited by data retention policies. Use soft deletion.");
    }

    public function deleteAccountOnly(string $customerId)
    {
        $stmt = $this->db->prepare("UPDATE accounts SET status = 'closed', updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$customerId]);
    }

    public function getUserProfile(int $userId)
    {
        $stmt = $this->db->prepare("
            SELECT id, customer_id, full_name, email, phone, status, dob, gender, father_name,
                   mother_name, marital_status, nationality, profile_picture,
                   occupation, annual_income, source_of_income, street, city, state, pincode, country,
                   id_type, id_number, id_front_path, id_back_path,
                   kyc_submitted_at, rejection_reason, is_deleted, deleted_at,
                   failed_attempts, locked_until, last_login_at, last_login_ip,
                   created_at, updated_at
            FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserAccounts(string $customerId)
    {
        $stmt = $this->db->prepare("
            SELECT id, account_no, account_type, balance, interest_rate, daily_limit,
                   status, opened_date, created_at
            FROM accounts WHERE user_id = ?
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAccountsRecentTransactions(array $accountIds, int $limit)
    {
        $placeholders = implode(',', array_fill(0, count($accountIds), '?'));
        $stmt = $this->db->prepare("
            SELECT
                t.txn_id, t.type, t.amount, t.balance_after, t.description,
                t.status, t.created_at,
                COALESCE(fa.account_no, '') as from_account,
                COALESCE(ta.account_no, '') as to_account
            FROM transactions t
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            WHERE t.from_account_id IN ($placeholders) OR t.to_account_id IN ($placeholders)
            ORDER BY t.created_at DESC
            LIMIT ?
        ");
        $params = array_merge($accountIds, $accountIds);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k + 1, $v);
        }
        $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDashboardStats(): array
    {
        $active_status = \App\Domain\Enums\AccountStatusEnum::ACTIVE->value;
        $pending_status = \App\Domain\Enums\AccountStatusEnum::PENDING->value;
        $under_review_status = \App\Domain\Enums\AccountStatusEnum::UNDER_REVIEW->value;
        $suspended_status = \App\Domain\Enums\AccountStatusEnum::SUSPENDED->value;

        $pending_users = (int)$this->getSingleStat("SELECT COUNT(*) FROM users WHERE status IN ('{$pending_status}', '{$under_review_status}') AND is_deleted = 0");

        return [
            'total_users'        => (int)$this->getSingleStat("SELECT COUNT(*) FROM users WHERE is_deleted = 0"),
            'active_users'       => (int)$this->getSingleStat("SELECT COUNT(*) FROM users WHERE status = '{$active_status}' AND is_deleted = 0"),
            'pending_users'      => $pending_users,
            'pending_approvals'  => $pending_users, // Alias for backward compatibility with frontend
            'suspended_users'    => (int)$this->getSingleStat("SELECT COUNT(*) FROM users WHERE status = '{$suspended_status}' AND is_deleted = 0"),
            'total_staff'        => (int)$this->getSingleStat("SELECT COUNT(*) FROM staff"),
            'total_branches'     => (int)$this->getSingleStat("SELECT COUNT(*) FROM branches")
        ];
    }

    public function getCachedDashboardStats(): array
    {
        $deposit = \App\Domain\Enums\TransactionTypeEnum::DEPOSIT->value;
        $withdrawal = \App\Domain\Enums\TransactionTypeEnum::WITHDRAWAL->value;
        $transfer = \App\Domain\Enums\TransactionTypeEnum::TRANSFER->value;
        $interest = \App\Domain\Enums\TransactionTypeEnum::INTEREST_CREDIT->value;
        $completed = \App\Domain\Enums\AccountStatusEnum::COMPLETED->value;
        $active = \App\Domain\Enums\AccountStatusEnum::ACTIVE->value;

        return [
            'transactions_today' => (int)$this->getSingleStat("SELECT COUNT(*) FROM transactions WHERE DATE(created_at) = CURDATE()"),
            'transactions_month' => (int)$this->getSingleStat("SELECT COUNT(*) FROM transactions WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"),
            'deposits_today'     => (float)$this->getSingleStat("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = '{$deposit}' AND status = '{$completed}' AND DATE(created_at) = CURDATE()"),
            'withdrawals_today'  => (float)$this->getSingleStat("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = '{$withdrawal}' AND status = '{$completed}' AND DATE(created_at) = CURDATE()"),
            'transfers_today'    => (float)$this->getSingleStat("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = '{$transfer}' AND status = '{$completed}' AND DATE(created_at) = CURDATE()"),
            'locked_accounts'    => (int)$this->getSingleStat("SELECT COUNT(*) FROM users WHERE locked_until > NOW() AND is_deleted = 0"),
            'total_balance'      => (float)$this->getSingleStat("SELECT COALESCE(SUM(balance), 0) FROM accounts WHERE status = '{$active}'"),
            'total_accounts'     => (int)$this->getSingleStat("SELECT COUNT(*) FROM accounts WHERE status = '{$active}'"),
            'interest_credited'  => (float)$this->getSingleStat("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = '{$interest}' AND status = '{$completed}'"),
            'soft_deleted_users' => (int)$this->getSingleStat("SELECT COUNT(*) FROM users WHERE is_deleted = 1")
        ];
    }

    public function getTotalInterestCredited(): float
    {
        $interest = \App\Domain\Enums\TransactionTypeEnum::INTEREST_CREDIT->value;
        $completed = \App\Domain\Enums\AccountStatusEnum::COMPLETED->value;
        return (float)$this->getSingleStat("SELECT SUM(amount) FROM transactions WHERE type = '{$interest}' AND status = '{$completed}'");
    }

    public function getEarningAccountsCount(): int
    {
        $active = \App\Domain\Enums\AccountStatusEnum::ACTIVE->value;
        return (int)$this->getSingleStat("SELECT COUNT(*) FROM accounts WHERE account_type = 'savings' AND status = '{$active}'");
    }

    public function getThisMonthInterest(): float
    {
        $interest = \App\Domain\Enums\TransactionTypeEnum::INTEREST_CREDIT->value;
        $completed = \App\Domain\Enums\AccountStatusEnum::COMPLETED->value;
        return (float)$this->getSingleStat("SELECT SUM(amount) FROM transactions WHERE type = '{$interest}' AND status = '{$completed}' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    }

    public function getLastMonthInterest(): float
    {
        $interest = \App\Domain\Enums\TransactionTypeEnum::INTEREST_CREDIT->value;
        $completed = \App\Domain\Enums\AccountStatusEnum::COMPLETED->value;
        return (float)$this->getSingleStat("SELECT SUM(amount) FROM transactions WHERE type = '{$interest}' AND status = '{$completed}' AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)");
    }

    public function getHistoricalMonthInterest(int $monthsAgo): float
    {
        $interest = \App\Domain\Enums\TransactionTypeEnum::INTEREST_CREDIT->value;
        $completed = \App\Domain\Enums\AccountStatusEnum::COMPLETED->value;
        return (float)$this->getSingleStat("SELECT SUM(amount) FROM transactions WHERE type = '{$interest}' AND status = '{$completed}' AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL {$monthsAgo} MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL {$monthsAgo} MONTH)");
    }
}
