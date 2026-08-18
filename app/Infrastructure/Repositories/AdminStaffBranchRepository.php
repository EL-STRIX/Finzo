<?php

namespace App\Infrastructure\Repositories;

use PDO;

class AdminStaffBranchRepository extends BaseRepository implements \App\Domain\Contracts\Repositories\AdminStaffBranchRepositoryInterface
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
        $stmt = $this->db->prepare("SELECT * FROM branches WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$res) return null;
        // Return object-compatible with stdClass / model
        $obj = new \App\Domain\Models\Branch();
        foreach ($res as $k => $v) {
            $obj->$k = $v;
        }
        return $obj;
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
            INSERT INTO branches (name, branch_code, state, description, manager_id, cert_path, type, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'custom', 'active', NOW())
        ");
        $name = $data['name'] ?? ($data['branch_name'] ?? '');
        $code = $data['branch_code'] ?? '';
        $state = $data['state'] ?? '';
        $desc = $data['description'] ?? null;
        $managerId = !empty($data['manager_id']) ? (int)$data['manager_id'] : null;
        $certPath = $data['cert_path'] ?? null;

        $stmt->execute([$name, $code, $state, $desc, $managerId, $certPath]);
        return (int)$this->db->lastInsertId();
    }

    public function updateBranchRecord(int $id, array $data): bool
    {
        if (empty($data)) return false;
        
        $fields = [];
        $values = [];
        $allowed = ['name', 'state', 'description', 'manager_id', 'cert_path', 'status'];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = ?";
                $values[] = $data[$col];
            }
        }
        if (empty($fields)) return false;

        $fields[] = "updated_at = NOW()";
        $values[] = $id;
        
        $sql = "UPDATE branches SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function updateBranchStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE branches SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function getBranchDetailWithStats(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT b.*,
                s.id as manager_id, s.full_name as manager_name, s.staff_id as manager_staff_id, s.personal_email as manager_email, s.profile_photo as manager_avatar,
                (SELECT COUNT(*) FROM staff st WHERE st.branch_id = b.id AND st.is_deleted = 0) as staff_count,
                (SELECT COUNT(*) FROM transactions t JOIN staff st ON t.processed_by_staff_id = st.id WHERE st.branch_id = b.id AND t.created_at >= b.created_at) as txn_count
            FROM branches b
            LEFT JOIN staff s ON b.manager_id = s.id
            WHERE b.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return null;

        if (!empty($row['manager_name'])) {
            $parts = explode(' ', $row['manager_name']);
            $row['manager_initials'] = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
        } else {
            $row['manager_initials'] = '??';
        }

        return $row;
    }

    public function findBranchByManagerId(int $managerId): ?array
    {
        $stmt = $this->db->prepare("SELECT id, name, branch_code, status FROM branches WHERE manager_id = ? LIMIT 1");
        $stmt->execute([$managerId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
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
        return (int)($stmt->fetchColumn() ?: 0);
    }

    public function getLastStaffId()
    {
        return $this->getMaxStaffId();
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
        $stmt = $this->db->prepare("UPDATE staff SET status = 'suspended', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$staffId]);
    }

    public function reinstateStaff(int $staffId)
    {
        $stmt = $this->db->prepare("UPDATE staff SET status = 'active', updated_at = NOW() WHERE id = ?");
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
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $res ? ($res['password_hash'] ?? null) : null;
    }

    public function deleteBranchAndUnassignStaff(int $branchId): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE staff SET branch_id = NULL WHERE branch_id = ?");
            $stmt->execute([$branchId]);

            $stmt = $this->db->prepare("DELETE FROM branches WHERE id = ?");
            $stmt->execute([$branchId]);
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            // If foreign key constraint prevents hard delete, fallback to status = 'inactive'
            $stmt = $this->db->prepare("UPDATE branches SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$branchId]);
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
            SELECT b.*,
                s.id as manager_id, s.full_name as manager_name, s.staff_id as manager_staff_id, s.personal_email as manager_email, s.profile_photo as manager_avatar,
                (SELECT COUNT(*) FROM staff st WHERE st.branch_id = b.id) as staff_count,
                (SELECT COUNT(*) FROM transactions t JOIN staff st ON t.processed_by_staff_id = st.id WHERE st.branch_id = b.id AND t.created_at >= b.created_at) as txn_count
            FROM branches b LEFT JOIN staff s ON b.manager_id = s.id WHERE {$where_sql} ORDER BY b.created_at DESC
        ");
        $stmt->execute($params);
        $branches = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt_stats = $this->db->query("
            SELECT COUNT(*) as total, 
                   COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                   COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive,
                   (SELECT COUNT(*) FROM staff WHERE branch_id IS NOT NULL AND is_deleted = 0) as total_staff,
                   (SELECT COUNT(*) FROM transactions WHERE processed_by_staff_id IN (SELECT id FROM staff WHERE branch_id IS NOT NULL)) as total_txns
            FROM branches
        ");
        $stats = $stmt_stats->fetch(\PDO::FETCH_ASSOC);

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

    public function updateStaff(int $id, array $data)
    {
        if (empty($data)) return false;
        
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        
        $sql = "UPDATE staff SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function getBranchesList(string $search, string $type, string $status)
    {
        return $this->getBranchesWithStats($search, $type, $status)['branches'];
    }

    public function getStaffList(array $filters, int $page = 1, int $perPage = 20): array
    {
        $search   = trim($filters['search'] ?? '');
        $status   = trim($filters['status'] ?? 'all');
        $role     = trim($filters['role'] ?? ($filters['designation'] ?? 'all'));
        $branchId = intval($filters['branch_id'] ?? 0);
        $filter   = trim($filters['filter'] ?? '');

        $whereClauses = ["s.is_deleted = 0"];
        $params = [];

        if ($status !== 'all' && in_array($status, ['active', 'suspended'])) {
            $whereClauses[] = "s.status = ?";
            $params[] = $status;
        }

        if ($role !== 'all' && !empty($role)) {
            $whereClauses[] = "s.designation = ?";
            $params[] = $role;
        }

        if ($branchId > 0) {
            $whereClauses[] = "s.branch_id = ?";
            $params[] = $branchId;
        } elseif ($filter === 'unassigned') {
            $whereClauses[] = "s.branch_id IS NULL";
        }

        if (!empty($search)) {
            $whereClauses[] = "(s.full_name LIKE ? OR s.personal_email LIKE ? OR s.staff_id LIKE ? OR s.phone LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $whereSql = implode(' AND ', $whereClauses);

        // Count matching records
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM staff s WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $pages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        // Fetch paginated staff records
        $sql = "
            SELECT s.id, s.staff_id, s.full_name, s.personal_email, s.phone, s.designation, s.department,
                   s.status, s.profile_photo, s.date_of_joining, s.last_login_at, s.created_at,
                   b.id as branch_id, b.name as branch_name, b.branch_code,
                   (SELECT id FROM branches WHERE manager_id = s.id LIMIT 1) as managed_branch_id,
                   (SELECT name FROM branches WHERE manager_id = s.id LIMIT 1) as managed_branch_name,
                   (SELECT COUNT(*) FROM transactions WHERE processed_by_staff_id = s.id AND status = 'completed') as txn_count,
                   (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE processed_by_staff_id = s.id AND status = 'completed') as txn_amount
            FROM staff s
            LEFT JOIN branches b ON b.id = s.branch_id
            WHERE {$whereSql}
            ORDER BY s.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $fetchParams = $params;
        $fetchParams[] = $perPage;
        $fetchParams[] = $offset;

        $stmt = $this->db->prepare($sql);
        // Bind integer parameters for LIMIT and OFFSET
        $idx = 1;
        foreach ($params as $param) {
            $stmt->bindValue($idx++, $param);
        }
        $stmt->bindValue($idx++, $perPage, \PDO::PARAM_INT);
        $stmt->bindValue($idx++, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $staffList = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch global stats
        $totalStaff = (int)$this->db->query("SELECT COUNT(*) FROM staff WHERE is_deleted = 0")->fetchColumn();
        $activeStaff = (int)$this->db->query("SELECT COUNT(*) FROM staff WHERE status = 'active' AND is_deleted = 0")->fetchColumn();
        $suspendedStaff = (int)$this->db->query("SELECT COUNT(*) FROM staff WHERE status = 'suspended' AND is_deleted = 0")->fetchColumn();
        $txnsToday = (int)$this->db->query("SELECT COUNT(*) FROM transactions WHERE DATE(created_at) = CURDATE()")->fetchColumn();

        $resolveStaffUrl = function(?string $path, string $subDir) {
            if (empty($path)) return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            $cleanPath = ltrim($path, '/');
            if (str_starts_with($cleanPath, 'Finzo/')) {
                return '/' . $cleanPath;
            }
            if (str_starts_with($cleanPath, 'uploads/')) {
                return '/Finzo/' . $cleanPath;
            }
            return '/Finzo/uploads/' . $subDir . '/' . $cleanPath;
        };

        foreach ($staffList as &$s) {
            $s['profile_photo_url'] = $resolveStaffUrl($s['profile_photo'] ?? null, 'staff-photos');
            $nameParts = explode(' ', trim($s['full_name'] ?? ''));
            $s['initials'] = strtoupper(substr($nameParts[0] ?? '', 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
        }

        return [
            'staff' => $staffList,
            'total' => $total,
            'pages' => $pages,
            'current_page' => $page,
            'stats' => [
                'total_staff' => $totalStaff,
                'active_staff' => $activeStaff,
                'suspended_staff' => $suspendedStaff,
                'transactions_today' => $txnsToday
            ]
        ];
    }

    public function exportStaffGenerator(array $filters): \Generator
    {
        $search   = trim($filters['search'] ?? '');
        $status   = trim($filters['status'] ?? 'all');
        $role     = trim($filters['role'] ?? ($filters['designation'] ?? 'all'));
        $branchId = intval($filters['branch_id'] ?? 0);
        $filter   = trim($filters['filter'] ?? '');

        $whereClauses = ["s.is_deleted = 0"];
        $params = [];

        if ($status !== 'all' && in_array($status, ['active', 'suspended'])) {
            $whereClauses[] = "s.status = ?";
            $params[] = $status;
        }

        if ($role !== 'all' && !empty($role)) {
            $whereClauses[] = "s.designation = ?";
            $params[] = $role;
        }

        if ($branchId > 0) {
            $whereClauses[] = "s.branch_id = ?";
            $params[] = $branchId;
        } elseif ($filter === 'unassigned') {
            $whereClauses[] = "s.branch_id IS NULL";
        }

        if (!empty($search)) {
            $whereClauses[] = "(s.full_name LIKE ? OR s.personal_email LIKE ? OR s.staff_id LIKE ? OR s.phone LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $whereSql = implode(' AND ', $whereClauses);

        $sql = "
            SELECT s.id, s.staff_id, s.full_name, s.personal_email, s.phone, s.designation, s.department,
                   s.status, s.gender, s.date_of_birth, s.emergency_contact_name, s.emergency_contact_phone,
                   s.date_of_joining, s.street, s.city, s.state, s.pincode, s.country, s.id_type, s.id_number,
                   s.created_by, s.last_login_at, s.last_login_ip, s.failed_attempts, s.created_at, s.updated_at,
                   b.name as branch_name, b.branch_code,
                   (SELECT COUNT(*) FROM transactions WHERE processed_by_staff_id = s.id AND status = 'completed') as txn_count,
                   (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE processed_by_staff_id = s.id AND status = 'completed') as txn_volume,
                   (SELECT COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) FROM transactions WHERE processed_by_staff_id = s.id AND status = 'completed') as total_deposits,
                   (SELECT COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) FROM transactions WHERE processed_by_staff_id = s.id AND status = 'completed') as total_withdrawals
            FROM staff s
            LEFT JOIN branches b ON b.id = s.branch_id
            WHERE {$whereSql}
            ORDER BY s.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $crypto = new \App\Shared\Security\EncryptionService();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $decryptedId = $row['id_number'];
            if (!empty($decryptedId)) {
                try {
                    $decryptedId = $crypto->decrypt($decryptedId) ?: $decryptedId;
                } catch (\Throwable $e) {
                    // Fallback if plaintext or decryption fails
                }
            }

            yield [
                'Staff ID'                 => $row['staff_id'] ?? 'N/A',
                'Full Name'                => $row['full_name'] ?? 'N/A',
                'Email Address'            => $row['personal_email'] ?? 'N/A',
                'Phone Number'             => $row['phone'] ?? 'N/A',
                'Designation / Role'       => $row['designation'] ?? 'N/A',
                'Department'               => $row['department'] ?? 'N/A',
                'Branch Name'              => $row['branch_name'] ?? 'Unassigned',
                'Branch Code'              => $row['branch_code'] ?? 'N/A',
                'Account Status'           => strtoupper($row['status'] ?? 'ACTIVE'),
                'Gender'                   => $row['gender'] ?? 'N/A',
                'Date of Birth'            => !empty($row['date_of_birth']) ? date('Y-m-d', strtotime($row['date_of_birth'])) : 'N/A',
                'Date of Joining'          => !empty($row['date_of_joining']) ? date('Y-m-d', strtotime($row['date_of_joining'])) : 'N/A',
                'Emergency Contact Name'   => $row['emergency_contact_name'] ?? 'N/A',
                'Emergency Contact Phone'  => $row['emergency_contact_phone'] ?? 'N/A',
                'Residential Street'       => $row['street'] ?? 'N/A',
                'City'                     => $row['city'] ?? 'N/A',
                'State'                    => $row['state'] ?? 'N/A',
                'Pincode'                  => $row['pincode'] ?? 'N/A',
                'Country'                  => $row['country'] ?? 'India',
                'ID Document Type'         => $row['id_type'] ?? 'N/A',
                'ID Document Number'       => $decryptedId ?: 'N/A',
                'Total Transactions'       => (int)($row['txn_count'] ?? 0),
                'Total Volume (INR)'       => number_format((float)($row['txn_volume'] ?? 0), 2, '.', ''),
                'Total Deposits (INR)'     => number_format((float)($row['total_deposits'] ?? 0), 2, '.', ''),
                'Total Withdrawals (INR)'  => number_format((float)($row['total_withdrawals'] ?? 0), 2, '.', ''),
                'Last Login Date'          => !empty($row['last_login_at']) ? date('Y-m-d H:i:s', strtotime($row['last_login_at'])) : 'Never',
                'Last Login IP'            => $row['last_login_ip'] ?? 'N/A',
                'Failed Login Attempts'    => (int)($row['failed_attempts'] ?? 0),
                'Created At'               => !empty($row['created_at']) ? date('Y-m-d H:i:s', strtotime($row['created_at'])) : 'N/A',
                'Last Updated At'          => !empty($row['updated_at']) ? date('Y-m-d H:i:s', strtotime($row['updated_at'])) : 'N/A'
            ];
        }
    }

    public function getStaffDetailComprehensive(int $staffId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone,
                   s.designation, s.department, s.status, s.profile_photo, s.created_at,
                   s.last_login_at, s.last_login_ip, s.street, s.city, s.state, s.pincode, s.country,
                   s.gender, s.date_of_birth, s.emergency_contact_name, s.emergency_contact_phone,
                   s.date_of_joining, s.created_by, s.failed_attempts, s.locked_until,
                   s.id_type, s.id_number, s.front_image, s.back_image,
                   s.temp_pass_status, s.updated_at, s.is_deleted, s.deleted_at,
                   b.id as branch_id, b.name as branch_name, b.branch_code, b.state as branch_state,
                   COALESCE(ca.full_name, cs.full_name, s.created_by, 'System') as created_by_name
            FROM staff s
            LEFT JOIN branches b ON b.id = s.branch_id
            LEFT JOIN admin_users ca ON s.created_by = ca.admin_id
            LEFT JOIN staff cs ON s.created_by = cs.staff_id
            WHERE s.id = ?
        ");
        $stmt->execute([$staffId]);
        $staff = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$staff) {
            return null;
        }

        // Decrypt ID Number if present
        if (!empty($staff['id_number'])) {
            try {
                $crypto = new \App\Shared\Security\EncryptionService();
                $staff['id_number'] = $crypto->decrypt($staff['id_number']) ?: $staff['id_number'];
            } catch (\Throwable $e) {
                // Keep raw
            }
        }

        // Resolve suspension reason from audit log if suspended
        $staff['suspension_reason'] = null;
        $staff['suspended_by_name'] = null;
        if ($staff['status'] === 'suspended') {
            $auditStmt = $this->db->prepare("
                SELECT details, actor_id, actor_role, created_at
                FROM audit_log
                WHERE (entity_id IN (?, ?) OR actor_id IN (?, ?)) AND action_type = 'staff_suspended'
                ORDER BY created_at DESC LIMIT 1
            ");
            $auditStmt->execute([$staff['staff_id'], (string)$staffId, $staff['staff_id'], (string)$staffId]);
            $suspensionLog = $auditStmt->fetch(\PDO::FETCH_ASSOC);
            if ($suspensionLog) {
                $details = $suspensionLog['details'] ?? '';
                if (preg_match('/Reason:\s*(.*)$/i', $details, $matches)) {
                    $staff['suspension_reason'] = trim($matches[1]);
                } else {
                    $staff['suspension_reason'] = !empty($details) ? $details : 'Administrative Review';
                }
                $staff['suspended_at'] = $suspensionLog['created_at'];
                $staff['suspended_by_name'] = 'Administrator (ID: ' . $suspensionLog['actor_id'] . ')';
            }
        }

        $resolveStaffUrl = function(?string $path, string $subDir) {
            if (empty($path)) return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            $cleanPath = ltrim($path, '/');
            if (str_starts_with($cleanPath, 'Finzo/')) {
                return '/' . $cleanPath;
            }
            if (str_starts_with($cleanPath, 'uploads/')) {
                return '/Finzo/' . $cleanPath;
            }
            return '/Finzo/uploads/' . $subDir . '/' . $cleanPath;
        };

        $staff['profile_photo_url'] = $resolveStaffUrl($staff['profile_photo'] ?? null, 'staff-photos');
        $staff['front_image_url'] = $resolveStaffUrl($staff['front_image'] ?? null, 'staff-documents');
        $staff['back_image_url'] = $resolveStaffUrl($staff['back_image'] ?? null, 'staff-documents');

        // Overall Performance
        $perfStmt = $this->db->prepare("
            SELECT COUNT(*) as total_transactions,
                   COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
                   COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals
            FROM transactions
            WHERE processed_by_staff_id = ? AND status = 'completed'
        ");
        $perfStmt->execute([$staffId]);
        $performance = $perfStmt->fetch(\PDO::FETCH_ASSOC) ?: [
            'total_transactions' => 0, 'total_deposits' => 0, 'total_withdrawals' => 0
        ];

        // Today's Shift Performance
        $todayStmt = $this->db->prepare("
            SELECT COUNT(*) as total_transactions,
                   COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
                   COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals,
                   COUNT(CASE WHEN type = 'deposit' THEN 1 END) as deposit_count,
                   COUNT(CASE WHEN type = 'withdrawal' THEN 1 END) as withdrawal_count
            FROM transactions
            WHERE processed_by_staff_id = ? AND status = 'completed' AND DATE(created_at) = CURDATE()
        ");
        $todayStmt->execute([$staffId]);
        $todayPerformance = $todayStmt->fetch(\PDO::FETCH_ASSOC) ?: [
            'total_transactions' => 0, 'total_deposits' => 0, 'total_withdrawals' => 0,
            'deposit_count' => 0, 'withdrawal_count' => 0
        ];

        // Shift History — use dedicated staff_shifts table; fall back to transaction-based dates
        $shiftStmt = $this->db->prepare("
            SELECT ss.shift_date, ss.clock_in as login_time, ss.clock_out as logout_time, ss.status as shift_status, ss.notes
            FROM staff_shifts ss
            WHERE ss.staff_id = ?
            ORDER BY ss.shift_date DESC
            LIMIT 10
        ");
        $shiftStmt->execute([$staff['staff_id']]);
        $shifts = $shiftStmt->fetchAll(\PDO::FETCH_ASSOC);

        // If no dedicated shifts, derive from transaction activity
        if (empty($shifts)) {
            $shiftStmt = $this->db->prepare("
                SELECT DATE(created_at) as shift_date, MIN(created_at) as login_time, MAX(created_at) as logout_time,
                       'completed' as shift_status, NULL as notes
                FROM transactions
                WHERE processed_by_staff_id = ?
                GROUP BY shift_date
                ORDER BY shift_date DESC
                LIMIT 10
            ");
            $shiftStmt->execute([$staffId]);
            $shifts = $shiftStmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        $shiftCountStmt = $this->db->prepare("SELECT COUNT(*) FROM staff_shifts WHERE staff_id = ?");
        $shiftCountStmt->execute([$staff['staff_id']]);
        $totalShifts = (int)$shiftCountStmt->fetchColumn();
        if ($totalShifts === 0) {
            $txnShiftStmt = $this->db->prepare("SELECT COUNT(DISTINCT DATE(created_at)) FROM transactions WHERE processed_by_staff_id = ?");
            $txnShiftStmt->execute([$staffId]);
            $totalShifts = (int)$txnShiftStmt->fetchColumn();
        }

        // Recent Transactions (Top 8)
        $txnStmt = $this->db->prepare("
            SELECT t.txn_id, t.type, t.amount, t.status, t.created_at, t.description, t.reference_note,
                   COALESCE(ta.account_no, fa.account_no, 'N/A') as account_no,
                   COALESCE(tu.full_name, fu.full_name, 'N/A') as customer_name
            FROM transactions t
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN users fu ON fa.user_id = fu.customer_id
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            LEFT JOIN users tu ON ta.user_id = tu.customer_id
            WHERE t.processed_by_staff_id = ?
            ORDER BY t.created_at DESC
            LIMIT 8
        ");
        $txnStmt->execute([$staffId]);
        $transactions = $txnStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Recent Staff Security & Activity Logs (Logins, Logouts, Security Operations, Shifts, Governance)
        $actStmt = $this->db->prepare("
            SELECT id, action_type, entity_id, entity_type, details, ip_address, created_at
            FROM audit_log
            WHERE (actor_id = ? OR actor_id = ? OR (entity_id IN (?, ?) AND entity_type = 'staff'))
              AND action_type NOT LIKE 'transaction_%'
              AND action_type NOT LIKE 'TRANSACTION_%'
              AND action_type NOT IN ('customer_cash_deposit', 'customer_cash_withdrawal')
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $actStmt->execute([(string)$staffId, $staff['staff_id'], (string)$staffId, $staff['staff_id']]);
        $activities = $actStmt->fetchAll(\PDO::FETCH_ASSOC);

        $address = [
            'street'  => $staff['street'] ?? '',
            'city'    => $staff['city'] ?? '',
            'state'   => $staff['state'] ?? '',
            'pincode' => $staff['pincode'] ?? '',
            'country' => $staff['country'] ?? 'India'
        ];

        $documents = [];
        if (!empty($staff['id_type']) || !empty($staff['id_number']) || !empty($staff['front_image'])) {
            $documents[] = [
                'id_type'     => $staff['id_type'] ?? 'ID Document',
                'id_number'   => $staff['id_number'] ?? '',
                'front_image' => $staff['front_image'] ?? null,
                'back_image'  => $staff['back_image'] ?? null
            ];
        }

        return [
            'staff'             => $staff,
            'performance'       => $performance,
            'today_performance' => $todayPerformance,
            'shifts'            => $shifts,
            'total_shifts'      => $totalShifts,
            'transactions'      => $transactions,
            'activities'        => $activities,
            'address'           => $address,
            'documents'         => $documents
        ];
    }
}

