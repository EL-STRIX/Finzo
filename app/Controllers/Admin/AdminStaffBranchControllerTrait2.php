<?php
namespace App\Controllers\Admin;

use App\Services\AdminStaffBranchService;
use Exception;
use PDOException;
use PDO;

trait AdminStaffBranchControllerTrait2 {
    public function uploadStaffPhoto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        $staff_id = intval($_POST['staff_id'] ?? 0);
        if (!$staff_id) json_response(['success' => false, 'message' => 'Staff ID is required'], 422);

        try {
            $pdo = $this->service->getRepo()->getPDO();
            $stmt = $pdo->prepare("SELECT id, profile_photo FROM staff WHERE id = ?");
            $stmt->execute([$staff_id]);
            $staff = $stmt->fetch();

            if (!$staff) json_response(['success' => false, 'message' => 'Staff member not found'], 404);
            if (empty($_FILES['photo']['name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) json_response(['success' => false, 'message' => 'Photo file is required'], 422);
            
            $file = $_FILES['photo'];
            $allowed_types = ['image/jpeg', 'image/png'];
            if (!in_array($file['type'], $allowed_types)) json_response(['success' => false, 'message' => 'Only JPG and PNG images are allowed'], 422);
            if ($file['size'] > 5242880) json_response(['success' => false, 'message' => 'File size exceeds 5MB limit'], 422);

            if ($staff['profile_photo']) {
                $old_path = dirname(__DIR__, 3) . '/' . $staff['profile_photo'];
                if (file_exists($old_path)) @unlink($old_path);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'staff_photo_' . $staff_id . '_' . time() . '.' . $ext;
            $target_dir = dirname(__DIR__, 3) . '/uploads/staff-photos/';
            
            if (!move_uploaded_file($file['tmp_name'], $target_dir . $filename)) json_response(['success' => false, 'message' => 'Failed to save uploaded file'], 500);

            $new_photo_path = 'uploads/staff-photos/' . $filename;
            $stmt = $pdo->prepare("UPDATE staff SET profile_photo = ? WHERE id = ?");
            $stmt->execute([$new_photo_path, $staff_id]);

            json_response(['success' => true, 'message' => 'Photo updated successfully', 'photo_url' => APP_URL . '/' . $new_photo_path]);
        } catch (PDOException $e) {
            json_response(['success' => false, 'message' => 'Failed to upload photo'], 500);
        }
    }

    public function staffDetail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        $staff_id = intval($_GET['staff_id'] ?? $_GET['id'] ?? 0);
        if (!$staff_id) json_response(['success' => false, 'message' => 'Staff ID is required'], 422);
        
        $pdo = $this->service->getRepo()->getPDO();
        try {
            $stmt = $pdo->prepare("
                SELECT s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone, 
                       s.designation, s.department, s.status,
                       s.gender, s.date_of_birth, s.emergency_contact_name, s.emergency_contact_phone,
                       s.date_of_joining, s.created_by,
                       s.last_login_at, s.last_login_ip, s.created_at, s.profile_photo,
                       s.street, s.city, s.state, s.pincode, s.country,
                       s.id_type, s.id_number, s.front_image, s.back_image,
                       s.failed_attempts, s.locked_until, s.suspension_reason, s.suspended_by,
                       b.name as branch_name, b.branch_code, b.state as branch_state,
                       COALESCE(creator_admin.full_name, creator_staff.full_name, s.created_by, 'System') as created_by_name,
                       COALESCE(suspender_admin.full_name, s.suspended_by) as suspended_by_name
                FROM staff s
                LEFT JOIN branches b ON s.branch_id = b.id
                LEFT JOIN admin_users creator_admin ON s.created_by = creator_admin.admin_id
                LEFT JOIN staff creator_staff ON s.created_by = creator_staff.staff_id
                LEFT JOIN admin_users suspender_admin ON s.suspended_by = suspender_admin.admin_id
                WHERE s.id = ?
            ");
            $stmt->execute([$staff_id]);
            $staff = $stmt->fetch();
            if (!$staff) json_response(['success' => false, 'message' => 'Staff member not found'], 404);

            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_transactions,
                    COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
                    COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals,
                    COUNT(CASE WHEN type = 'deposit' THEN 1 END) as deposit_count,
                    COUNT(CASE WHEN type = 'withdrawal' THEN 1 END) as withdrawal_count
                FROM transactions WHERE processed_by_staff_id = ? AND status = 'completed'
            ");
            $stmt->execute([$staff_id]);
            $performance = $stmt->fetch();

            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_transactions,
                    COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
                    COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals,
                    COUNT(CASE WHEN type = 'deposit' THEN 1 END) as deposit_count,
                    COUNT(CASE WHEN type = 'withdrawal' THEN 1 END) as withdrawal_count
                FROM transactions WHERE processed_by_staff_id = ? AND status = 'completed' AND DATE(created_at) = CURDATE()
            ");
            $stmt->execute([$staff_id]);
            $today_performance = $stmt->fetch();

            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT DATE(created_at)) FROM transactions WHERE processed_by_staff_id = ?");
            $stmt->execute([$staff_id]);
            $total_shifts = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("
                SELECT t.txn_id, t.type, t.amount, t.status, t.created_at,
                    COALESCE(a.account_no, 'N/A') as account_no, COALESCE(u.full_name, 'N/A') as customer_name
                FROM transactions t
                LEFT JOIN accounts a ON COALESCE(t.to_account_id, t.from_account_id) = a.id
                LEFT JOIN users u ON a.user_id = u.customer_id
                WHERE t.processed_by_staff_id = ? ORDER BY t.created_at DESC LIMIT 20
            ");
            $stmt->execute([$staff_id]);
            $transactions = $stmt->fetchAll();

            $stmt = $pdo->prepare("
                SELECT DATE(created_at) as shift_date, MIN(created_at) as login_time, MAX(created_at) as logout_time
                FROM (
                    SELECT created_at FROM transactions WHERE processed_by_staff_id = :id
                    UNION ALL
                    SELECT created_at FROM audit_log WHERE actor_id = :actor_id AND actor_role = 'Staff'
                ) as activities
                GROUP BY shift_date ORDER BY shift_date DESC LIMIT 5
            ");
            $stmt->execute(['id' => $staff_id, 'actor_id' => $staff['staff_id']]);
            $shifts = $stmt->fetchAll();

            $documents = [];
            if (!empty($staff['id_type'])) {
                $documents[] = ['id_type' => $staff['id_type'], 'id_number' => $staff['id_number'], 'front_image' => $staff['front_image'], 'back_image' => $staff['back_image']];
            }

            $address = ['street' => $staff['street'], 'city' => $staff['city'], 'state' => $staff['state'], 'pincode' => $staff['pincode'], 'country' => $staff['country']];

            $stmt = $pdo->prepare("SELECT id, action_type, entity_id, entity_type, details, ip_address, created_at FROM audit_log WHERE actor_id = ? AND actor_role IN ('Staff', 'staff') ORDER BY created_at DESC LIMIT 20");
            $stmt->execute([$staff['staff_id']]);
            $activities = $stmt->fetchAll();

            json_response([
                'success' => true, 'staff' => $staff, 'performance' => $performance, 'today_performance' => $today_performance,
                'total_shifts' => $total_shifts, 'shifts' => $shifts, 'transactions' => $transactions,
                'activities' => $activities, 'documents' => $documents, 'address' => $address
            ]);
        } catch (PDOException $e) {
            json_response(['success' => false, 'message' => 'Failed to load staff details'], 500);
        }
    }

    public function getStaffList() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        $pdo = $this->service->getRepo()->getPDO();
        try {
            $search    = clean_input($_GET['search'] ?? '');
            $status    = clean_input($_GET['status'] ?? 'all');
            $filter    = clean_input($_GET['filter'] ?? '');
            $branch_id = intval($_GET['branch_id'] ?? 0);
            $page      = max(1, intval($_GET['page'] ?? 1));
            $per_page  = 20;

            $where_clauses = ["1=1", "s.status != 'suspended'"];
            $params = [];
            if ($branch_id > 0) { $where_clauses[] = "s.branch_id = ?"; $params[] = $branch_id; }
            if ($filter === 'unassigned') $where_clauses[] = "s.branch_id IS NULL";
            if (!empty($search)) {
                $where_clauses[] = "(s.full_name LIKE ? OR s.personal_email LIKE ? OR s.staff_id LIKE ? OR s.phone LIKE ?)";
                $s = "%{$search}%"; $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
            }

            $where_sql = implode(' AND ', $where_clauses);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff s WHERE {$where_sql}");
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            $pages  = max(1, ceil($total / $per_page));
            $offset = ($page - 1) * $per_page;

            $stmt = $pdo->prepare("
                SELECT s.id, s.staff_id, s.full_name, s.personal_email, s.phone, s.designation, s.department,
                    s.status, s.profile_photo, s.date_of_joining, s.last_login_at, b.name as branch_name,
                    (SELECT COUNT(*) FROM transactions WHERE processed_by_staff_id = s.id) as txn_count,
                    (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE processed_by_staff_id = s.id AND status = 'completed') as txn_amount
                FROM staff s LEFT JOIN branches b ON b.id = s.branch_id WHERE {$where_sql} ORDER BY s.created_at DESC LIMIT {$per_page} OFFSET {$offset}
            ");
            $stmt->execute($params);
            $staff_list = $stmt->fetchAll();

            $total_staff = $pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn();
            $active_staff = $pdo->query("SELECT COUNT(*) FROM staff WHERE status = 'active'")->fetchColumn();
            $suspended_staff = $pdo->query("SELECT COUNT(*) FROM staff WHERE status = 'suspended'")->fetchColumn();
            $txns_today = $pdo->query("SELECT COUNT(*) FROM transactions WHERE DATE(created_at) = CURDATE()")->fetchColumn();

            $base_url = APP_URL . '/';
            foreach ($staff_list as &$s) {
                if ($s['profile_photo']) $s['profile_photo_url'] = $base_url . $s['profile_photo'];
                $name_parts = explode(' ', $s['full_name']);
                $s['initials'] = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
            }

            json_response([
                'success' => true, 'staff' => $staff_list, 'total' => $total, 'pages' => $pages, 'current_page' => $page,
                'stats' => ['total_staff' => (int)$total_staff, 'active_staff' => (int)$active_staff, 'suspended_staff' => (int)$suspended_staff, 'transactions_today' => (int)$txns_today]
            ]);
        } catch (PDOException $e) {
            json_response(['success' => false, 'message' => 'Failed to load staff list'], 500);
        }
    }
}
