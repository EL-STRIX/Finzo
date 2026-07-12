<?php
namespace App\Controllers\Admin;

use App\Services\AdminStaffBranchService;
use Exception;
use PDOException;
use PDO;

trait AdminStaffBranchControllerTrait3 {
    public function suspendedStaffDetail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        $staff_id = intval($_GET['id'] ?? 0);
        if (!$staff_id) json_response(['success' => false, 'message' => 'Staff ID is required'], 422);

        $pdo = $this->service->getRepo()->getPDO();
        try {
            $stmt = $pdo->prepare("
                SELECT s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone, s.designation as role, s.status,
                       s.suspension_reason, s.suspended_by, s.updated_at as suspended_at, s.created_at as onboarding_date, s.profile_photo, b.name as branch_name
                FROM staff s LEFT JOIN branches b ON s.branch_id = b.id WHERE s.id = ? AND s.status = 'suspended'
            ");
            $stmt->execute([$staff_id]);
            $staff = $stmt->fetch();

            if (!$staff) json_response(['success' => false, 'message' => 'Suspended staff record not found'], 404);

            $name_parts = explode(' ', $staff['full_name']);
            $staff['initials'] = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
            $staff['suspended_at'] = date('d M Y, H:i', strtotime($staff['suspended_at']));
            $staff['onboarding_date'] = date('d M Y', strtotime($staff['onboarding_date']));
            $staff['duration'] = 'Indefinite';

            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_count, COALESCE(SUM(amount), 0) as total_volume,
                    COUNT(CASE WHEN type = 'deposit' THEN 1 END) as deposit_count,
                    COUNT(CASE WHEN type = 'withdrawal' THEN 1 END) as withdrawal_count
                FROM transactions WHERE processed_by_staff_id = ? AND status = 'completed'
            ");
            $stmt->execute([$staff_id]);
            $stats = $stmt->fetch();

            $staff['lifetime_volume'] = '₹' . number_format($stats['total_volume'], 2);
            $staff['deposit_count'] = $stats['deposit_count'];
            $staff['withdrawal_count'] = $stats['withdrawal_count'];
            $staff['avg_daily_volume'] = '₹' . number_format($stats['total_volume'] / 30, 2);

            $stmt = $pdo->prepare("SELECT type, amount, status, created_at as timestamp FROM transactions WHERE processed_by_staff_id = ? ORDER BY created_at DESC LIMIT 10");
            $stmt->execute([$staff_id]);
            $history = $stmt->fetchAll();

            foreach ($history as &$h) {
                $h['amount'] = '₹' . number_format($h['amount'], 2);
                $h['timestamp'] = date('d M Y, H:i', strtotime($h['timestamp']));
                $h['type'] = strtoupper($h['type']);
                $h['status'] = strtoupper($h['status']);
            }

            json_response(['status' => 'success', 'data' => ['staff' => $staff, 'history' => $history]]);
        } catch (PDOException $e) {
            json_response(['success' => false, 'message' => 'Failed to load details'], 500);
        }
    }

    public function getSuspendedStaff() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        $pdo = $this->service->getRepo()->getPDO();
        try {
            $search     = clean_input($_GET['search'] ?? '');
            $reason     = clean_input($_GET['reason'] ?? '');
            $start_date = clean_input($_GET['start_date'] ?? '');
            $end_date   = clean_input($_GET['end_date'] ?? '');
            $page       = max(1, intval($_GET['page'] ?? 1));
            $per_page   = min(50, max(10, intval($_GET['per_page'] ?? 20)));

            $where_clauses = ["s.status = 'suspended'"];
            $params = [];

            if (!empty($search)) {
                $where_clauses[] = "(s.full_name LIKE ? OR s.personal_email LIKE ? OR s.staff_id LIKE ?)";
                $s = "%{$search}%"; $params[] = $s; $params[] = $s; $params[] = $s;
            }
            if (!empty($reason)) { $where_clauses[] = "s.suspension_reason = ?"; $params[] = $reason; }
            if (!empty($start_date)) { $where_clauses[] = "s.updated_at >= ?"; $params[] = $start_date . ' 00:00:00'; }
            if (!empty($end_date)) { $where_clauses[] = "s.updated_at <= ?"; $params[] = $end_date . ' 23:59:59'; }

            $where_sql = implode(' AND ', $where_clauses);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff s WHERE {$where_sql}");
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            $pages  = max(1, ceil($total / $per_page));
            $offset = ($page - 1) * $per_page;

            $stmt = $pdo->prepare("
                SELECT s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone, s.designation as role, s.status,
                       s.suspension_reason, s.suspended_by, s.updated_at, s.profile_photo, b.name as branch_name
                FROM staff s LEFT JOIN branches b ON s.branch_id = b.id WHERE {$where_sql} ORDER BY s.updated_at DESC LIMIT {$per_page} OFFSET {$offset}
            ");
            $stmt->execute($params);
            $staff_list = $stmt->fetchAll();

            foreach ($staff_list as &$s) {
                $name_parts = explode(' ', $s['full_name']);
                $s['initials'] = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
                $s['suspended_at'] = date('d M Y, H:i', strtotime($s['updated_at']));
                $s['suspension_reason'] = $s['suspension_reason'] ?: 'Administrative Review';
                $s['suspended_by'] = $s['suspended_by'] ?: 'Admin';
                $s['duration'] = 'Indefinite';
            }

            json_response(['status' => 'success', 'data' => ['staff' => $staff_list, 'total' => $total, 'pages' => $pages, 'current_page' => $page]]);
        } catch (PDOException $e) {
            json_response(['success' => false, 'message' => 'Failed to load suspended staff'], 500);
        }
    }

    public function getBranches() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        $pdo = $this->service->getRepo()->getPDO();
        try {
            $search = clean_input($_GET['search'] ?? '');
            $type   = clean_input($_GET['type'] ?? 'all');
            $status = clean_input($_GET['status'] ?? 'all');
            
            $where_clauses = ["1=1"];
            $params = [];
            
            if ($type !== 'all' && in_array($type, ['system', 'custom'])) { $where_clauses[] = "b.type = ?"; $params[] = $type; }
            if ($status !== 'all' && in_array($status, ['active', 'inactive'])) { $where_clauses[] = "b.status = ?"; $params[] = $status; }
            if (!empty($search)) {
                $where_clauses[] = "(b.name LIKE ? OR b.branch_code LIKE ? OR b.state LIKE ?)";
                $s = "%{$search}%"; $params[] = $s; $params[] = $s; $params[] = $s;
            }

            $where_sql = implode(' AND ', $where_clauses);
            $stmt = $pdo->prepare("
                SELECT b.id, b.name, b.branch_code, b.state, b.city, b.address, b.description, b.type, b.status, b.cert_path, b.created_at,
                    s.id as manager_id, s.full_name as manager_name, s.staff_id as manager_staff_id, s.personal_email as manager_email, s.profile_photo as manager_avatar,
                    (SELECT COUNT(*) FROM staff st WHERE st.branch_id = b.id) as staff_count,
                    (SELECT COUNT(*) FROM transactions t JOIN staff st ON t.processed_by_staff_id = st.id WHERE st.branch_id = b.id) as txn_count
                FROM branches b LEFT JOIN staff s ON b.manager_id = s.id WHERE {$where_sql} ORDER BY b.created_at DESC
            ");
            $stmt->execute($params);
            $branches = $stmt->fetchAll();

            $stmt_stats = $pdo->query("
                SELECT COUNT(*) as total, COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                    (SELECT COUNT(*) FROM staff WHERE branch_id IS NOT NULL) as total_staff
                FROM branches
            ");
            $stats = $stmt_stats->fetch();

            foreach ($branches as &$b) {
                if (!empty($b['manager_name'])) {
                    $parts = explode(' ', $b['manager_name']);
                    $b['manager_initials'] = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                } else {
                    $b['manager_initials'] = '??';
                }
            }

            json_response(['success' => true, 'branches' => $branches, 'stats' => ['total' => (int)$stats['total'], 'active' => (int)$stats['active'], 'total_staff' => (int)$stats['total_staff']]]);
        } catch (PDOException $e) {
            json_response(['success' => false, 'message' => 'Failed to load branches'], 500);
        }
    }
}
