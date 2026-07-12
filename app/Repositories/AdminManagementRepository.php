<?php
namespace App\Repositories;

use PDO;

class AdminManagementRepository extends BaseRepository {
    
    public function getAdminByEmail(string $email) {
        return $this->fetchOne("SELECT id FROM admin_users WHERE email = ?", [$email]);
    }

    public function getAdminById(int $id) {
        return $this->fetchOne("SELECT * FROM admin_users WHERE id = ?", [$id]);
    }

    public function getAdminByAdminId(string $adminId) {
        return $this->fetchOne("SELECT * FROM admin_users WHERE admin_id = ?", [$adminId]);
    }

    public function getMaxAdminIdNumber(): int {
        $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(admin_id, 4) AS UNSIGNED)) as max_id FROM admin_users");
        return (int)$stmt->fetchColumn();
    }

    public function createAdmin(array $data) {
        $sql = "INSERT INTO admin_users (
            admin_id, full_name, date_of_birth, gender, profile_photo, email, phone, 
            password_hash, security_question, security_answer_hash, security_question_set,
            admin_role, access_level, status, created_by
        ) VALUES (
            :admin_id, :full_name, :date_of_birth, :gender, :profile_photo, :email, :phone, 
            :password_hash, :security_question, :security_answer_hash, 1,
            :admin_role, :access_level, 'active', :created_by
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status) {
        return $this->execute("UPDATE admin_users SET status = ? WHERE id = ?", [$status, $id]);
    }

    public function deleteAdmin(int $id) {
        return $this->execute("DELETE FROM admin_users WHERE id = ?", [$id]);
    }

    public function resetPassword(int $id, string $hash) {
        return $this->execute("
            UPDATE admin_users 
            SET password_hash = ?, 
                last_password_changed = NOW(), 
                failed_attempts = 0,
                temp_password_expiry = DATE_ADD(NOW(), INTERVAL 24 HOUR)
            WHERE id = ?
        ", [$hash, $id]);
    }

    public function getAdminDetail(int $id) {
        return $this->fetchOne("
            SELECT 
                a.id, a.admin_id, a.full_name, a.email, a.phone, a.date_of_birth, a.gender, a.profile_photo,
                a.admin_role, a.access_level, a.status, a.two_fa_enabled, 
                a.last_login_at, a.last_login_ip, a.last_password_changed, a.failed_attempts, a.created_at,
                (SELECT full_name FROM admin_users WHERE admin_id = a.created_by) AS created_by_name
            FROM admin_users a
            WHERE a.id = ? AND a.admin_role != 'super_admin'
        ", [$id]);
    }
    
    public function getAdminActivity(string $adminId, int $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT action_type, details, ip_address, created_at, entity_id, entity_type
            FROM audit_log
            WHERE actor_id = ? AND actor_role IN ('Super Admin', 'Admin', 'Moderator', 'super_admin', 'admin', 'moderator')
            ORDER BY created_at DESC LIMIT " . (int)$limit . "
        ");
        $stmt->execute([$adminId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getActivityCount(string $adminId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_actions
            FROM audit_log
            WHERE actor_id = ? AND actor_role IN ('Super Admin', 'Admin', 'Moderator', 'super_admin', 'admin', 'moderator')
        ");
        $stmt->execute([$adminId]);
        return (int)$stmt->fetchColumn();
    }

    public function getAllAdmins() {
        return $this->fetchAll("
            SELECT 
                a.id, a.admin_id, a.full_name, a.email, a.phone, a.gender, a.profile_photo, 
                a.admin_role, a.access_level, a.status, a.last_login_at, a.created_at,
                (SELECT full_name FROM admin_users WHERE admin_id = a.created_by) AS created_by_name
            FROM admin_users a
            WHERE a.admin_role != 'super_admin'
            ORDER BY a.created_at DESC
        ");
    }

    public function getLockedAdmins() {
        return $this->fetchAll("
            SELECT id, full_name, email, admin_role, locked_at, lock_reason, 
                   answer_attempts, answer_locked_until, security_question_set
            FROM admin_users
            WHERE account_locked = 1
            ORDER BY locked_at DESC
        ");
    }

    public function updateAdminProfileField(int $id, string $field, $value) {
        return $this->execute("UPDATE admin_users SET {$field} = ?, updated_at = NOW() WHERE id = ?", [$value, $id]);
    }

    public function updateAdminPassword(int $id, string $hash) {
        return $this->execute("UPDATE admin_users SET password_hash = ?, last_password_changed = NOW(), updated_at = NOW() WHERE id = ?", [$hash, $id]);
    }
    
    public function updateProfileFields(int $id, array $fields, array $params) {
        $query = "UPDATE admin_users SET " . implode(", ", $fields) . " WHERE id = ?";
        $params[] = $id;
        return $this->execute($query, $params);
    }
    
    public function getAdminProfile(int $id) {
        return $this->fetchOne("
            SELECT id, admin_id, full_name, email, phone, date_of_birth, gender, profile_photo,
                   admin_role, access_level, status, two_fa_enabled, 
                   last_login_at, last_login_ip, last_password_changed, failed_attempts,
                   security_question, security_question_set, created_at
            FROM admin_users 
            WHERE id = ?
        ", [$id]);
    }
    
    public function updateTwoFa(int $id, int $enabled) {
        return $this->execute("UPDATE admin_users SET two_fa_enabled = ? WHERE id = ?", [$enabled, $id]);
    }
}
