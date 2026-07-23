<?php

namespace App\Infrastructure\Repositories;

class AuthRepository extends BaseRepository
{
    // =====================================================
    // USER METHODS
    // =====================================================

    public function findUserByIdentity(string $identity): array|false
    {
        return $this->fetchOne("
            SELECT u.id, u.customer_id, u.full_name, u.email, u.phone, u.password_hash, u.status, u.failed_attempts, u.locked_until
            FROM users u
            LEFT JOIN accounts a ON u.customer_id = a.user_id
            WHERE (u.email = ? OR a.account_no = ?) AND u.is_deleted = 0
            LIMIT 1
        ", [$identity, $identity]);
    }

    public function findUserByEmail(string $email): array|false
    {
        return $this->fetchOne("SELECT id, customer_id, full_name, email FROM users WHERE email = ? AND is_deleted = 0", [$email]);
    }

    public function findUserByEmailAny(string $email): array|false
    {
        return $this->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    }

    public function findUserByPhone(string $phone): array|false
    {
        return $this->fetchOne("SELECT id FROM users WHERE phone = ?", [$phone]);
    }

    public function fetchActiveAccount(string $customer_id): array|false
    {
        return $this->fetchOne("
            SELECT account_no, account_type, balance, status
            FROM accounts
            WHERE user_id = ? AND status IN ('active', 'pending')
            ORDER BY status = 'active' DESC
            LIMIT 1
        ", [$customer_id]);
    }

    public function lockUser(int $id, int $attempts, string $lock_until): bool
    {
        return $this->execute(
            "UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?",
            [$attempts, $lock_until, $id]
        );
    }

    public function updateFailedAttempts(int $id, int $attempts): bool
    {
        return $this->execute("UPDATE users SET failed_attempts = ? WHERE id = ?", [$attempts, $id]);
    }

    public function updateSuccessfulLogin(int $id, string $ip): bool
    {
        return $this->execute("
            UPDATE users SET failed_attempts = 0, locked_until = NULL, last_login_at = NOW(), last_login_ip = ?
            WHERE id = ?
        ", [$ip, $id]);
    }

    public function getNextCustomerId(): string
    {
        $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(customer_id, 9) AS UNSIGNED)) as max_id FROM users WHERE customer_id LIKE 'FNZ-USR-%' FOR UPDATE");
        $max_seq = $stmt->fetchColumn() ?: 0;
        return 'FNZ-USR-' . str_pad((string)($max_seq + 1), 3, '0', STR_PAD_LEFT);
    }

    public function createUser(string $customer_id, string $fullName, string $email, string $phone, string $password_hash): string
    {
        $this->execute("
            INSERT INTO users (customer_id, full_name, email, phone, password_hash, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ", [$customer_id, $fullName, $email, $phone, $password_hash]);
        return $this->db->lastInsertId();
    }

    public function createNotification(string $customer_id, string $type, string $title, string $message): bool
    {
        return $this->execute("
            INSERT INTO notifications (user_id, type, title, message)
            VALUES (?, ?, ?, ?)
        ", [$customer_id, $type, $title, $message]);
    }

    // =====================================================
    // ADMIN METHODS
    // =====================================================

    public function findAdminByEmail(string $email): array|false
    {
        return $this->fetchOne("
            SELECT id, admin_id, full_name, email, profile_photo, password_hash, status, admin_role, 
                   failed_attempts, account_locked, locked_at, auto_unlock_at, answer_locked_until, security_question_set,
                   temp_password_expiry
            FROM admin_users
            WHERE email = ?
        ", [$email]);
    }

    public function autoUnlockAdmin(int $id): bool
    {
        return $this->execute("
            UPDATE admin_users 
            SET account_locked = 0, failed_attempts = 0, locked_at = NULL, lock_reason = NULL, auto_unlock_at = NULL 
            WHERE id = ?
        ", [$id]);
    }

    public function lockAdmin(int $id): string|null
    {
        $this->execute("
            UPDATE admin_users 
            SET account_locked = 1, 
                locked_at = NOW(), 
                failed_attempts = 3, 
                lock_reason = 'failed_password',
                auto_unlock_at = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
            WHERE id = ?
        ", [$id]);
        $row = $this->fetchOne("SELECT auto_unlock_at FROM admin_users WHERE id = ?", [$id]);
        return $row ? $row['auto_unlock_at'] : null;
    }

    public function logAdminLock(string $adminId, string $ip, int $id): bool
    {
        return $this->execute("
            INSERT INTO admin_lock_log (admin_id, event_type, ip_address, details) 
            SELECT admin_id, 'locked_password', ?, 'Locked after 3 failed password attempts'
            FROM admin_users WHERE id = ?
        ", [$ip, $id]);
    }

    public function updateAdminFailedAttempts(int $id, int $attempts): bool
    {
        return $this->execute("UPDATE admin_users SET failed_attempts = ? WHERE id = ?", [$attempts, $id]);
    }

    public function updateAdminSuccessfulLogin(int $id, string $ip): bool
    {
        return $this->execute("
            UPDATE admin_users 
            SET failed_attempts = 0, account_locked = 0, last_login_at = NOW(), last_login_ip = ? 
            WHERE id = ?
        ", [$ip, $id]);
    }

    // =====================================================
    // STAFF METHODS
    // =====================================================

    public function findStaffByIdentity(string $identity): array|false
    {
        return $this->fetchOne("
            SELECT id, staff_id, full_name, personal_email as email, phone, password_hash, status, designation as role, 
                   failed_attempts, locked_until, temp_pass_status
            FROM staff
            WHERE (personal_email = ? OR staff_id = ?) AND is_deleted = 0
        ", [$identity, $identity]);
    }

    public function lockStaff(int $id, int $attempts, string $lock_until): bool
    {
        return $this->execute("
            UPDATE staff SET failed_attempts = ?, locked_until = ?, temp_pass_status = 'pending' WHERE id = ?
        ", [$attempts, $lock_until, $id]);
    }

    public function updateStaffFailedAttempts(int $id, int $attempts): bool
    {
        return $this->execute("UPDATE staff SET failed_attempts = ? WHERE id = ?", [$attempts, $id]);
    }

    public function updateStaffSuccessfulLogin(int $id, string $ip): bool
    {
        return $this->execute("
            UPDATE staff SET failed_attempts = 0, locked_until = NULL, temp_pass_status = 'none', last_login_at = NOW(), last_login_ip = ?
            WHERE id = ?
        ", [$ip, $id]);
    }

    // =====================================================
    // PASSWORD RESETS
    // =====================================================

    public function deletePasswordResets(string $customer_id): bool
    {
        return $this->execute("DELETE FROM password_resets WHERE user_id = ?", [$customer_id]);
    }

    public function createPasswordReset(string $customer_id, string $token, string $expires_at): bool
    {
        return $this->execute("
            INSERT INTO password_resets (user_id, token, expires_at, created_at)
            VALUES (?, ?, ?, NOW())
        ", [$customer_id, $token, $expires_at]);
    }

    public function findPasswordReset(string $token): array|false
    {
        return $this->fetchOne("
            SELECT pr.id, pr.user_id, pr.expires_at, u.full_name, u.email
            FROM password_resets pr
            JOIN users u ON pr.user_id = u.customer_id
            WHERE pr.token = ?
        ", [$token]);
    }

    public function deletePasswordResetById(int $id): bool
    {
        return $this->execute("DELETE FROM password_resets WHERE id = ?", [$id]);
    }

    public function resetUserPassword(string $customer_id, string $password_hash): bool
    {
        return $this->execute("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE customer_id = ?", [$password_hash, $customer_id]);
    }

    public function resetUserAttempts(string $customer_id): bool
    {
        return $this->execute("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE customer_id = ?", [$customer_id]);
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

    public function getDb(): \PDO
    {
        return $this->db;
    }
}
