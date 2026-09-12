<?php

namespace App\Infrastructure\Repositories;

use PDO;

class UserRepository extends BaseRepository implements \App\Domain\Contracts\Repositories\UserRepositoryInterface
{


    public function fetchAccountLookup(string $accountNumber)
    {
        $stmt = $this->db->prepare("
            SELECT u.full_name, a.account_type, a.status
            FROM accounts a
            JOIN users u ON a.user_id = u.customer_id
            WHERE a.account_no = ?
        ");
        $stmt->execute([$accountNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAccountSettings(int $userId)
    {
        $stmt = $this->db->prepare("SELECT a.id, a.daily_limit FROM accounts a JOIN users u ON a.user_id = u.customer_id WHERE u.id = ? AND a.status = 'active' LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchUsedToday(int $account_id)
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) 
            FROM transactions 
            WHERE from_account_id = ? 
              AND type = 'transfer'
              AND DATE(created_at) = CURDATE() 
              AND status IN ('completed', 'pending')
        ");
        $stmt->execute([$account_id]);
        return $stmt->fetchColumn();
    }

    public function updateDailyLimit(int $account_id, float $daily_limit)
    {
        $stmt = $this->db->prepare("UPDATE accounts SET daily_limit = ? WHERE id = ?");
        return $stmt->execute([$daily_limit, $account_id]);
    }

    public function checkIdNumberExists(string $idType, string $idNumber, int $excludeUserId = 0): bool
    {
        $crypto = new \App\Shared\Security\EncryptionService();
        $encryptedId = $crypto->encryptSearchable($idNumber);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id_type = ? AND id_number = ? AND id != ?");
        $stmt->execute([$idType, $encryptedId, $excludeUserId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function fetchAccountDetails(int $userId)
    {
        $stmt = $this->db->prepare("
            SELECT
                a.id, a.account_no, a.account_type, a.balance, a.interest_rate,
                a.daily_limit, a.status, a.opened_date, a.created_at,
                u.full_name, u.email, u.phone
            FROM accounts a
            JOIN users u ON a.user_id = u.customer_id
            WHERE u.id = ?
            ORDER BY a.status = 'active' DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchMiniStatement(int $account_id)
    {
        $stmt = $this->db->prepare("
            SELECT txn_id, type, amount, balance_after, description, status, created_at
            FROM transactions
            WHERE (from_account_id = ? OR to_account_id = ?) AND status = 'completed'
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$account_id, $account_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchTodayTransferTotal(int $account_id)
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as today_total
            FROM transactions
            WHERE from_account_id = ? AND type = 'transfer'
                AND status = 'completed'
                AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([$account_id]);
        return $stmt->fetchColumn();
    }

    public function getCustomerId(int $userId)
    {
        $stmt = $this->db->prepare("SELECT customer_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public function fetchBeneficiaries($userId)
    {
        $stmt = $this->db->prepare("
            SELECT id, nickname, account_no, bank_name, created_at
            FROM beneficiaries
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteBeneficiary($beneficiary_id, $userId)
    {
        $stmt = $this->db->prepare("DELETE FROM beneficiaries WHERE id = ? AND user_id = ?");
        $stmt->execute([$beneficiary_id, $userId]);
        return $stmt->rowCount();
    }

    public function countBeneficiaries($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM beneficiaries WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public function getBeneficiaryByAccount($userId, $accountNumber)
    {
        $stmt = $this->db->prepare("SELECT id FROM beneficiaries WHERE user_id = ? AND account_no = ?");
        $stmt->execute([$userId, $accountNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addBeneficiary($userId, $nickname, $accountNumber, $bank_name)
    {
        $stmt = $this->db->prepare("
            INSERT INTO beneficiaries (user_id, nickname, account_no, bank_name, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $nickname, $accountNumber, $bank_name]);
        return $this->db->lastInsertId();
    }

    public function getUserPasswordHash(int $userId)
    {
        $stmt = $this->db->prepare("SELECT customer_id, password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword(int $userId, string $new_hash)
    {
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$new_hash, $userId]);
    }

    // -- Complete Profile --
    public function getUserFiles(int $userId)
    {
        $stmt = $this->db->prepare("SELECT profile_picture, id_front_path, id_back_path FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getExistingAccountByCustomerId(string $customer_id)
    {
        $stmt = $this->db->prepare("SELECT id FROM accounts WHERE user_id = ?");
        $stmt->execute([$customer_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateAccountResubmission(string $account_type, float $initial_deposit, string $txn_pin_hash, string $customer_id)
    {
        $stmt = $this->db->prepare("
            UPDATE accounts SET
                account_type = ?, balance = ?, txn_pin_hash = ?, status = 'pending', updated_at = NOW()
            WHERE user_id = ? AND status IN ('pending', 'rejected')
        ");
        return $stmt->execute([$account_type, $initial_deposit, $txn_pin_hash, $customer_id]);
    }

    public function insertPendingAccount(string $customer_id, string $temp_account_no, string $account_type, float $initial_deposit, string $txn_pin_hash)
    {
        $stmt = $this->db->prepare("
            INSERT INTO accounts (user_id, account_no, account_type, balance, txn_pin_hash, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        return $stmt->execute([$customer_id, $temp_account_no, $account_type, $initial_deposit, $txn_pin_hash]);
    }

    public function getNextKycApplicationNo(string $year)
    {
        $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(application_no, 8) AS UNSIGNED)) FROM kyc_applications WHERE application_no LIKE 'KYC{$year}%'");
        return $stmt->fetchColumn() ?: 0;
    }

    public function getKycApplication(string $customer_id_str)
    {
        $stmt = $this->db->prepare("SELECT id, submission_count FROM kyc_applications WHERE user_id = ?");
        $stmt->execute([$customer_id_str]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // update user, update kyc, insert kyc etc logic will be handled inside repository transaction methods or directly in service for complex forms. Let's provide generic execute for them.
    public function executeQuery($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // -- Dashboard --
    public function getUserDashboardData(int $userId)
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.customer_id, u.full_name, u.email, u.phone, u.status, u.rejection_reason,
                   COALESCE(k.profile_picture, u.profile_picture) AS profile_picture,
                   u.created_at, u.updated_at
            FROM users u
            LEFT JOIN kyc_applications k ON u.customer_id = k.user_id
            WHERE u.id = ? AND u.is_deleted = 0
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActiveAccountByCustomerId(string $customer_id)
    {
        $stmt = $this->db->prepare("
            SELECT id, account_no, account_type, balance, interest_rate, daily_limit, status, opened_date
            FROM accounts
            WHERE user_id = ?
            ORDER BY status = 'active' DESC, created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$customer_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecentTransactions(int $account_id)
    {
        $stmt = $this->db->prepare("
            SELECT
                t.txn_id, t.type, t.amount, t.description, t.status, t.created_at,
                CASE 
                    WHEN t.type = ? AND t.from_account_id = ? THEN (SELECT u.full_name FROM accounts a JOIN users u ON a.user_id = u.customer_id WHERE a.id = t.to_account_id) 
                    WHEN t.type = ? AND t.to_account_id = ? THEN (SELECT u.full_name FROM accounts a JOIN users u ON a.user_id = u.customer_id WHERE a.id = t.from_account_id) 
                    ELSE '' 
                END as counterparty_name
            FROM transactions t
            WHERE t.from_account_id = ? OR t.to_account_id = ?
            ORDER BY t.created_at DESC LIMIT 5
        ");
        $type = \App\Domain\Enums\TransactionTypeEnum::TRANSFER->value;
        $stmt->execute([$type, $account_id, $type, $account_id, $account_id, $account_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTransactionToAccountId(string $transactionId)
    {
        $stmt = $this->db->prepare("SELECT to_account_id FROM transactions WHERE txn_id = ?");
        $stmt->execute([$transactionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMonthlyIncome(int $account_id)
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM transactions
            WHERE to_account_id = ?
                AND status = ?
                AND MONTH(created_at) = MONTH(CURDATE())
                AND YEAR(created_at) = YEAR(CURDATE())
        ");
        $stmt->execute([$account_id, \App\Domain\Enums\AccountStatusEnum::COMPLETED->value]);
        return $stmt->fetchColumn();
    }

    public function getMonthlyExpenses(int $account_id)
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM transactions
            WHERE from_account_id = ?
                AND status = ?
                AND MONTH(created_at) = MONTH(CURDATE())
                AND YEAR(created_at) = YEAR(CURDATE())
                AND description != 'Account Restore Charge'
        ");
        $stmt->execute([$account_id, \App\Domain\Enums\AccountStatusEnum::COMPLETED->value]);
        return $stmt->fetchColumn();
    }

    public function getUnreadNotificationCount($customer_id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$customer_id]);
        return $stmt->fetchColumn();
    }

    public function getPastRejections(string $customer_id)
    {
        $stmt = $this->db->prepare("
            SELECT details, created_at 
            FROM audit_log 
            WHERE action_type = 'kyc_rejected' AND entity_id = ? 
            ORDER BY id ASC
        ");
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -- Notifications --
    public function getNotificationsTotal($customer_id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
        $stmt->execute([$customer_id]);
        return $stmt->fetchColumn();
    }

    public function getNotifications($customer_id, int $per_page, int $offset)
    {
        $stmt = $this->db->prepare("
            SELECT id, type, title, message, is_read, created_at
            FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT {$per_page} OFFSET {$offset}
        ");
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markNotificationRead(int $notification_id, $customer_id)
    {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        return $stmt->execute([$notification_id, $customer_id]);
    }

    public function markAllNotificationsRead($customer_id)
    {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$customer_id]);
        return $stmt->rowCount();
    }

    public function deleteNotification(int $notification_id, $customer_id)
    {
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        return $stmt->execute([$notification_id, $customer_id]);
    }

    public function clearAllNotifications($customer_id)
    {
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE user_id = ?");
        $stmt->execute([$customer_id]);
        return $stmt->rowCount();
    }

    // -- Profile --
    public function getFullProfile(int $userId)
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.customer_id, u.full_name, u.email, u.phone, u.status,
                   u.branch_id,
                   COALESCE(b.name, 'Unassigned') as branch_name,
                   COALESCE(b.branch_code, '') as branch_code,
                   COALESCE(b.state, '') as branch_state,
                   COALESCE(k.dob, u.dob) as dob,
                   COALESCE(k.gender, u.gender) as gender,
                   COALESCE(k.father_name, u.father_name) as father_name,
                   COALESCE(k.mother_name, u.mother_name) as mother_name,
                   COALESCE(k.marital_status, u.marital_status) as marital_status,
                   COALESCE(k.nationality, u.nationality) as nationality,
                   COALESCE(k.source_of_income, u.source_of_income) as source_of_income,
                   COALESCE(k.profile_picture, u.profile_picture) as profile_picture,
                   COALESCE(k.occupation, u.occupation) as occupation,
                   COALESCE(k.annual_income, u.annual_income) as annual_income,
                   COALESCE(k.street, u.street) as street,
                   COALESCE(k.city, u.city) as city,
                   COALESCE(k.state, u.state) as state,
                   COALESCE(k.pincode, u.pincode) as pincode,
                   u.country,
                   COALESCE(k.id_type, u.id_type) as id_type,
                   COALESCE(k.id_number, u.id_number) as id_number,
                   COALESCE(k.id_front_path, u.id_front_path) as id_front_path,
                   COALESCE(k.id_back_path, u.id_back_path) as id_back_path,
                   u.rejection_reason,
                   u.kyc_submitted_at,
                   u.created_at
            FROM users u
            LEFT JOIN kyc_applications k ON u.customer_id = k.user_id
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE u.id = ? AND u.is_deleted = 0
        ");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($row && !empty($row['id_number'])) {
            $crypto = new \App\Shared\Security\EncryptionService();
            $row['id_number'] = $crypto->decrypt($row['id_number']);
        }
        
        return $row;
    }

    public function checkEmailExists(string $email, int $exclude_user_id)
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $exclude_user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function checkPhoneExists(string $phone, int $exclude_user_id)
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
        $stmt->execute([$phone, $exclude_user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile(int $userId, string $fullName, string $phone, string $email)
    {
        $stmt = $this->db->prepare("
            UPDATE users SET full_name = ?, phone = ?, email = ?, updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$fullName, $phone, $email, $userId]);
    }

    // -- Statement --
    public function getActiveAccount(int $userId)
    {
        $stmt = $this->db->prepare("
            SELECT a.id, a.account_no, a.account_type, a.balance, a.opened_date, u.full_name, a.daily_limit
            FROM accounts a
            JOIN users u ON a.user_id = u.customer_id
            WHERE u.id = ? AND a.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStatementTransactions(int $account_id, string $fromDate, string $toDate)
    {
        $stmt = $this->db->prepare("
            SELECT
                t.txn_id,
                t.type,
                t.amount,
                t.balance_after,
                t.description,
                t.reference_note,
                t.status,
                t.created_at,
                t.from_account_id,
                t.to_account_id,
                COALESCE(fa.account_no, '') as from_account_no,
                COALESCE(ta.account_no, '') as to_account_no,
                COALESCE(fu.full_name, '') as from_name,
                COALESCE(tu.full_name, '') as to_name
            FROM transactions t
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN users fu ON fa.user_id = fu.customer_id
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            LEFT JOIN users tu ON ta.user_id = tu.customer_id
            WHERE (t.from_account_id = ? OR t.to_account_id = ?)
                AND t.status = 'completed'
                AND t.created_at BETWEEN ? AND ?
            ORDER BY t.created_at ASC
        ");
        $stmt->execute([$account_id, $account_id, $fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOpeningBalance(int $account_id, string $fromDate)
    {
        $stmt = $this->db->prepare("
            SELECT balance_after
            FROM transactions
            WHERE (from_account_id = ? OR to_account_id = ?)
                AND status = 'completed'
                AND DATE(created_at) < ?
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$account_id, $account_id, $fromDate]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // -- Transactions --
    public function getTransactionsCount($where_sql, $params)
    {
        $count_sql = "SELECT COUNT(*) FROM transactions t WHERE {$where_sql}";
        $stmt = $this->db->prepare($count_sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getTransactionsList($where_sql, $params, $per_page, $offset)
    {
        $sql = "
            SELECT
                t.txn_id,
                t.type,
                t.amount,
                t.balance_after,
                t.dest_balance_after,
                t.description,
                t.reference_note,
                t.status,
                t.created_at,
                t.from_account_id,
                t.to_account_id,
                fa.account_no as from_account_no,
                COALESCE(fu.full_name, 'N/A') as from_name,
                ta.account_no as to_account_no,
                COALESCE(tu.full_name, 'N/A') as to_name
            FROM transactions t
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN users fu ON fa.user_id = fu.customer_id
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            LEFT JOIN users tu ON ta.user_id = tu.customer_id
            WHERE {$where_sql}
            ORDER BY t.created_at DESC
            LIMIT {$per_page} OFFSET {$offset}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -- Transfer --
    public function getAccountForUpdate(int $account_id)
    {
        $stmt = $this->db->prepare("
            SELECT a.id, a.account_no, a.balance, a.daily_limit, u.full_name, u.id as owner_user_id, u.customer_id
            FROM accounts a
            JOIN users u ON a.user_id = u.customer_id
            WHERE a.id = ? AND a.status = 'active'
            FOR UPDATE
        ");
        $stmt->execute([$account_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAccountIdByNumber(string $accountNumber)
    {
        $stmt = $this->db->prepare("
            SELECT a.id
            FROM accounts a
            WHERE a.account_no = ? AND a.status = 'active'
        ");
        $stmt->execute([$accountNumber]);
        return $stmt->fetchColumn();
    }

    public function updateAccountBalance(int $account_id, float $amount_change)
    {
        $stmt = $this->db->prepare("UPDATE accounts SET balance = balance + ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$amount_change, $account_id]);
    }

    public function insertTransaction(string $transactionId, int $from_id, int $to_id, float $amount, float $balance_after, float $dest_balance_after, string $description)
    {
        $stmt = $this->db->prepare("
            INSERT INTO transactions
                (txn_id, from_account_id, to_account_id, type, amount, balance_after, dest_balance_after, description, status, created_at)
            VALUES (?, ?, ?, 'transfer', ?, ?, ?, ?, 'completed', NOW())
        ");
        return $stmt->execute([
            $transactionId, $from_id, $to_id, $amount, $balance_after, $dest_balance_after, $description
        ]);
    }
}
