<?php

namespace App\Infrastructure\Repositories;

use PDO;

class AdminStaffBranchRepository implements \App\Domain\Contracts\Repositories\AdminStaffBranchRepositoryInterface
{
    public function getActiveBranches()
    {
        return $this->fetchAll("SELECT id, name, branch_code FROM branches WHERE status = 'active'", [], \App\Domain\Models\Branch::class);
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

    public function updateStaffBranch(int $staffId, int $branchId)
    {
        $stmt = $this->db->prepare("UPDATE staff SET branch_id = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$branchId, $staffId]);
    }

    public function unassignStaffFromBranch(int $branchId)
    {
        $stmt = $this->db->prepare("UPDATE staff SET branch_id = NULL WHERE branch_id = ?");
        return $stmt->execute([$branchId]);
    }

    public function deleteBranch(int $branchId)
    {
        $stmt = $this->db->prepare("UPDATE branches SET status = 'inactive' WHERE id = ?");
        return $stmt->execute([$branchId]);
    }

    public function getMaxStaffId()
    {
        $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(staff_id, 4) AS UNSIGNED)) as max_id FROM staff WHERE staff_id LIKE 'STF%'");
        return $stmt->fetchColumn() ?: 0;
    }

    public function getStaffDetail(int $staffId)
    {
        $staff = $this->fetchOne("
            SELECT s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone,
                   s.designation, s.department, s.status, s.profile_photo, s.created_at,
                   s.last_login_at, s.last_login_ip, s.street, s.city, s.state, s.pincode, s.country,
                   s.id_type, s.id_number, s.front_image, s.back_image, b.name as branch_name
            FROM staff s
            LEFT JOIN branches b ON b.id = s.branch_id
            WHERE s.id = ?
        ", [$staffId], \App\Domain\Models\Staff::class);
        
        if ($staff && !empty($staff->id_number)) {
            $crypto = new \App\Shared\Security\EncryptionService();
            $staff->id_number = $crypto->decrypt($staff->id_number);
        }
        
        return $staff;
    }

    public function getStaffPerformance(int $staffId)
    {
        return $this->fetchOne("
            SELECT COUNT(*) as total_transactions,
                   COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
                   COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals
            FROM transactions
            WHERE processed_by_staff_id = ? AND status = 'completed'
        ", [$staffId], \App\Domain\Models\Staff::class);
    }

    public function findStaffRecord(int $staffId)
    {
        return $this->fetchOne("SELECT id, staff_id, full_name, personal_email as email, status, profile_photo FROM staff WHERE id = ?", [$staffId], \App\Domain\Models\Staff::class);
    }

    public function updateStaffPassword(int $staffId, string $hash)
    {
        $stmt = $this->db->prepare("UPDATE staff SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$hash, $staffId]);
    }

    public function suspendStaff(int $staffId, string $reason, string $suspended_by)
    {
        $stmt = $this->db->prepare("UPDATE staff SET status = 'suspended', suspension_reason = ?, suspended_by = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$reason, $suspended_by, $staffId]);
    }

    public function reinstateStaff(int $staffId)
    {
        $stmt = $this->db->prepare("UPDATE staff SET status = 'active', suspension_reason = NULL, suspended_by = NULL, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$staffId]);
    }

    public function deleteStaff(int $staffId)
    {
        $stmt = $this->db->prepare("UPDATE staff SET is_deleted = 1, status = 'suspended', deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$staffId]);
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

    public function updateStaffPhoto(int $staffId, string $path)
    {
        $stmt = $this->db->prepare("UPDATE staff SET profile_photo = ? WHERE id = ?");
        return $stmt->execute([$path, $staffId]);
    }
    public function getAdminPasswordHash(int $adminId): ?string
    {
        $stmt = $this->db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
        $stmt->execute([$adminId]);
        $res = $stmt->fetch();
        return $res ? $res['password_hash'] : null;
    }

    public function deleteBranchAndUnassignStaff(int $branchId): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE staff SET branch_id = NULL WHERE branch_id = ?");
            $stmt->execute([$branchId]);

            $stmt = $this->db->prepare("UPDATE branches SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$branchId]);
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getBranchesWithStats(string $search, string $type, string $status): array
    {
        $where_clauses = ["1=1"];
        $params = [];

        if ($type !== 'all' && in_array($type, ['system', 'custom'])) {
            $where_clauses[] = "b.type = ?";
            $params[] = $type;
        }
        if ($status !== 'all' && in_array($status, ['active', 'inactive'])) {
            $where_clauses[] = "b.status = ?";
            $params[] = $status;
        }
        if (!empty($search)) {
            $where_clauses[] = "(b.name LIKE ? OR b.branch_code LIKE ? OR b.state LIKE ?)";
            $s = "%$search%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $where_sql = implode(' AND ', $where_clauses);
        $stmt = $this->db->prepare("
            SELECT b.id, b.name, b.branch_code, b.state, b.city, b.address, b.description, b.type, b.status, b.cert_path, b.created_at,
                s.id as manager_id, s.full_name as manager_name, s.staff_id as manager_staff_id, s.personal_email as manager_email, s.profile_photo as manager_avatar,
                (SELECT COUNT(*) FROM staff st WHERE st.branch_id = b.id) as staff_count,
                (SELECT COUNT(*) FROM transactions t JOIN staff st ON t.processed_by_staff_id = st.id WHERE st.branch_id = b.id) as txn_count
            FROM branches b LEFT JOIN staff s ON b.manager_id = s.id WHERE {$where_sql} ORDER BY b.created_at DESC
        ");
        $stmt->execute($params);
        $branches = $stmt->fetchAll();

        $stmt_stats = $this->db->query("
            SELECT COUNT(*) as total, COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                (SELECT COUNT(*) FROM staff WHERE branch_id IS NOT NULL) as total_staff
            FROM branches
        ");
        $stats = $stmt_stats->fetch();

        return ['branches' => $branches, 'stats' => $stats];
    }

    public function getDepartmentAnalytics()
    {
        $stmt = $this->db->query("
            SELECT department, COUNT(id) as staff_count, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_staff
            FROM staff
            GROUP BY department
            ORDER BY staff_count DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}