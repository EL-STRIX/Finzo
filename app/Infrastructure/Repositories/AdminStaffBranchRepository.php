<?php

namespace App\Infrastructure\Repositories;

use PDO;

class AdminStaffBranchRepository extends BaseRepository
{
    public function getPDO(): PDO
    {
        return $this->db;
    }

    public function findStaffByEmail(string $email)
    {
        return $this->fetchOne("SELECT id FROM staff WHERE personal_email = ?", [$email], \App\Domain\Models\Staff::class);
    }

    public function findBranchById(int $id)
    {
        return $this->fetchOne("SELECT id, name, type FROM branches WHERE id = ?", [$id], \App\Domain\Models\Staff::class);
    }

    public function findAdminById(int $id)
    {
        return $this->fetchOne("SELECT id, admin_id, password_hash, full_name, email, account_locked, auto_unlock_at, status FROM admin_users WHERE id = ?", [$id], \App\Domain\Models\Staff::class);
    }

    public function countBranchByCode(string $codePattern)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM branches WHERE branch_code LIKE ?");
        $stmt->execute([$codePattern]);
        return (int)$stmt->fetchColumn();
    }

    public function countExactBranchCode(string $code)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM branches WHERE branch_code = ?");
        $stmt->execute([$code]);
        return (int)$stmt->fetchColumn();
    }

    public function findActiveStaffById(int $id)
    {
        return $this->fetchOne("SELECT id FROM staff WHERE id = ? AND status = 'active'", [$id], \App\Domain\Models\Staff::class);
    }

    public function insertBranch(array $data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO branches (name, branch_code, state, city, address, description, manager_id, cert_path, type, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'custom', 'active', NOW())
        ");
        $stmt->execute([
            $data['branch_name'], $data['branch_code'], $data['state'], $data['city'],
            $data['address'], $data['description'], $data['manager_id'], $data['cert_path']
        ]);
        return $this->db->lastInsertId();
    }

    public function updateStaffBranch(int $staff_id, int $branch_id)
    {
        $stmt = $this->db->prepare("UPDATE staff SET branch_id = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$branch_id, $staff_id]);
    }

    public function unassignStaffFromBranch(int $branch_id)
    {
        $stmt = $this->db->prepare("UPDATE staff SET branch_id = NULL WHERE branch_id = ?");
        return $stmt->execute([$branch_id]);
    }

    public function deleteBranch(int $branch_id)
    {
        $stmt = $this->db->prepare("DELETE FROM branches WHERE id = ?");
        return $stmt->execute([$branch_id]);
    }

    public function getMaxStaffId()
    {
        $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(staff_id, 4) AS UNSIGNED)) as max_id FROM staff WHERE staff_id LIKE 'STF%'");
        return $stmt->fetchColumn() ?: 0;
    }

    public function getStaffDetail(int $staff_id)
    {
        return $this->fetchOne("
            SELECT s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone,
                   s.designation, s.department, s.status, s.profile_photo, s.created_at,
                   s.last_login_at, s.last_login_ip, s.street, s.city, s.state, s.pincode, s.country,
                   s.id_type, s.id_number, s.front_image, s.back_image, b.name as branch_name
            FROM staff s
            LEFT JOIN branches b ON b.id = s.branch_id
            WHERE s.id = ?
        ", [$staff_id], \App\Domain\Models\Staff::class);
    }

    public function getStaffPerformance(int $staff_id)
    {
        return $this->fetchOne("
            SELECT COUNT(*) as total_transactions,
                   COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
                   COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals
            FROM transactions
            WHERE processed_by_staff_id = ? AND status = 'completed'
        ", [$staff_id], \App\Domain\Models\Staff::class);
    }

    public function findStaffRecord(int $staff_id)
    {
        return $this->fetchOne("SELECT id, staff_id, full_name, personal_email as email, status, profile_photo FROM staff WHERE id = ?", [$staff_id], \App\Domain\Models\Staff::class);
    }

    public function updateStaffPassword(int $staff_id, string $hash)
    {
        $stmt = $this->db->prepare("UPDATE staff SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$hash, $staff_id]);
    }

    public function suspendStaff(int $staff_id, string $reason, string $suspended_by)
    {
        $stmt = $this->db->prepare("UPDATE staff SET status = 'suspended', suspension_reason = ?, suspended_by = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$reason, $suspended_by, $staff_id]);
    }

    public function reinstateStaff(int $staff_id)
    {
        $stmt = $this->db->prepare("UPDATE staff SET status = 'active', suspension_reason = NULL, suspended_by = NULL, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$staff_id]);
    }

    public function deleteStaff(int $staff_id)
    {
        $stmt = $this->db->prepare("DELETE FROM staff WHERE id = ?");
        return $stmt->execute([$staff_id]);
    }

    public function insertStaff(array $data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO staff (
                full_name, date_of_birth, gender, profile_photo,
                personal_email, phone, emergency_contact_name, emergency_contact_phone,
                staff_id, designation, department, branch_id,
                date_of_joining, password_hash, status, created_by,
                street, city, state, pincode, country,
                id_type, id_number, front_image, back_image
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, 'India', ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['full_name'], $data['date_of_birth'], $data['gender'], $data['profile_photo'],
            $data['personal_email'], $data['phone'], $data['emergency_contact_name'], $data['emergency_contact_phone'],
            $data['staff_id'], $data['designation'], $data['department'], $data['branch_id'],
            $data['date_of_joining'], $data['password_hash'], $data['created_by'],
            $data['street'], $data['city'], $data['state'], $data['pincode'],
            $data['id_type'], $data['id_number'], $data['front_image'], $data['back_image']
        ]);
        return $this->db->lastInsertId();
    }

    public function updateStaffPhoto(int $staff_id, string $path)
    {
        $stmt = $this->db->prepare("UPDATE staff SET profile_photo = ? WHERE id = ?");
        return $stmt->execute([$path, $staff_id]);
    }
}
