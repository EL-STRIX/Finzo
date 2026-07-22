<?php

namespace App\Presentation\Controllers\Admin;

use App\Shared\Security\SecurityService;

use App\Application\Services\AdminStaffBranchService;
use App\Shared\Core\Exceptions\ApiException;
use PDOException;
use Exception;

class AdminStaffController
{
    protected SecurityService $security;

    protected AdminStaffBranchService $service;

    public function __construct(AdminStaffBranchService $service, ?SecurityService $security = null)
    {
        $this->security = $security ?? new \App\Shared\Security\SecurityService();
        $this->service = $service;
    }

    protected function checkCsrf()
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';
        if (!$this->security->validateCsrfToken($csrf)) {
            throw new ApiException('Invalid CSRF token', 403);
        }
    }

    public function addStaff()
    {
        $this->checkCsrf();
        $adminId = $_SESSION['admin_id'];

        $data = [
            'full_name' => $this->security->cleanInput($_POST['full_name'] ?? ''),
            'date_of_birth' => $this->security->cleanInput($_POST['date_of_birth'] ?? ''),
            'gender' => $this->security->cleanInput($_POST['gender'] ?? ''),
            'personal_email' => $this->security->cleanInput($_POST['personal_email'] ?? ''),
            'phone' => $this->security->cleanInput($_POST['phone'] ?? ''),
            'emergency_contact_name' => $this->security->cleanInput($_POST['emergency_contact_name'] ?? ''),
            'emergency_contact_phone' => $this->security->cleanInput($_POST['emergency_contact_phone'] ?? ''),
            'designation' => $this->security->cleanInput($_POST['designation'] ?? 'Teller'),
            'branch_id' => ($_POST['branch_id'] ?? '' !== '' && $_POST['branch_id'] ?? '' !== 'null') ? intval($_POST['branch_id'] ?? '') : null,
            'date_of_joining' => $this->security->cleanInput($_POST['date_of_joining'] ?? date('Y-m-d')),
            'street' => $this->security->cleanInput($_POST['street'] ?? ''),
            'city' => $this->security->cleanInput($_POST['city'] ?? ''),
            'state' => $this->security->cleanInput($_POST['state'] ?? ''),
            'pincode' => $this->security->cleanInput($_POST['pincode'] ?? ''),
            'id_type' => $this->security->cleanInput($_POST['id_type'] ?? ''),
            'id_number' => $this->security->cleanInput($_POST['id_number'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];

        try {
            $result = $this->service->createStaff($data, $_FILES, $adminId);
            json_response(['success' => true, 'message' => 'Staff member added successfully', 'staff_id' => $result['staff_id'], 'staff_name' => $result['staff_name']], 201);
        } catch (\App\Domain\Exceptions\ValidationException $e) {
            json_response(['success' => false, 'message' => 'Validation failed', 'errors' => json_decode($e->getMessage(), true)], 422);
        } catch (ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ApiException('An error occurred while creating staff account: ' . $e->getMessage(), 500);
        }
    }

    public function getStaffDetail(): array
    {
        $staffId = intval($_GET['staff_id'] ?? $_GET['id'] ?? 0);
        if (!$staffId) {
            throw new ApiException('Staff ID is required', 422);
        }

        $staff = $this->service->getStaffDetail($staffId);
        if (!$staff) {
            throw new ApiException('Staff member not found', 404);
        }

        $base_url = APP_URL . '/';
        if ($staff['profile_photo']) {
            $staff['profile_photo_url'] = $base_url . $staff['profile_photo'];
        }
        if ($staff['front_image']) {
            $staff['front_image_url'] = $base_url . $staff['front_image'];
        }
        if ($staff['back_image']) {
            $staff['back_image_url'] = $base_url . $staff['back_image'];
        }

        $performance = $this->service->getStaffPerformance($staffId);
        return ['success' => true, 'staff' => $staff, 'performance' => $performance];
    }

    public function generateStaffPassword(): array
    {
        $this->checkCsrf();
        return ['success' => true, 'password' => $this->service->generateStrongPassword(12)];
    }

    public function getNextStaffId(): array
    {
        return ['success' => true, 'next_id' => $this->service->generateStaffId()];
    }

    public function resetStaffPassword(): array
    {
        $adminId = $_SESSION['admin_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $this->checkCsrf();

        $pdo = $this->service->getRepo()->getPDO();
        $this->security->checkAdminPermission($pdo, $adminId, 'reset_staff_password');

        $staffId = intval($input['staff_id'] ?? $_POST['staff_id'] ?? 0);
        $newPassword = $input['new_password'] ?? $_POST['new_password'] ?? '';

        if (!$staffId) {
            throw new ApiException('Staff ID is required', 422);
        }
        if (\App\Shared\Core\Validation\Validator::length($newPassword) < 8) {
            throw new ApiException('Password must be at least 8 characters', 422);
        }

        $stmt = $pdo->prepare("SELECT id, full_name, staff_id FROM staff WHERE id = ?");
        $stmt->execute([$staffId]);
        $staff = $stmt->fetch();

        if (!$staff) {
            throw new ApiException('Staff member not found', 404);
        }

        $password_hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE staff SET password_hash = ? WHERE id = ?");
        $stmt->execute([$password_hash, $staffId]);

        $this->security->writeAuditLog($pdo, 'staff_password_reset', $staffId, 'staff', $adminId, 'admin', "Password reset for staff: " . $staff['full_name'] . " (" . $staff['staff_id'] . ")");
        return ['success' => true, 'message' => 'Password updated successfully'];
    }

    public function staffAction(): array
    {
        $staffId = intval($_POST['staff_id'] ?? 0);
        $action   = $this->security->cleanInput($_POST['action'] ?? '');
        $reason   = $this->security->cleanInput($_POST['reason'] ?? '');

        if (!$staffId || \App\Shared\Core\Validation\Validator::isEmpty($action)) {
            throw new ApiException('Staff ID and action are required', 422);
        }
        if (!in_array($action, ['suspend', 'reinstate', 'reset_password', 'delete'])) {
            throw new ApiException('Invalid action', 422);
        }

        $adminId = $_SESSION['admin_id'];
        $password = $_POST['password'] ?? '';
        if (\App\Shared\Core\Validation\Validator::isEmpty($password)) {
            throw new ApiException('Admin password is required for this action', 422);
        }

        $pdo = $this->service->getRepo()->getPDO();
        if (in_array($action, ['suspend', 'reinstate', 'delete'])) {
            $this->security->checkAdminPermission($pdo, $adminId, 'suspend_staff');
        }

        $admin_stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
        $admin_stmt->execute([$adminId]);
        $admin = $admin_stmt->fetch();
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            throw new ApiException('Invalid administrative password. Access denied.', 403);
        }

        $stmt = $pdo->prepare("SELECT id, staff_id, full_name, personal_email as email, status FROM staff WHERE id = ?");
        $stmt->execute([$staffId]);
        $staff = $stmt->fetch();
        if (!$staff) {
            throw new ApiException('Staff member not found', 404);
        }

        if ($action === 'suspend') {
            if ($staff['status'] === 'suspended') {
                throw new ApiException('Staff is already suspended', 409);
            }
            $suspended_by = $_SESSION['admin_name'] ?? 'Admin';
            $stmt = $pdo->prepare("UPDATE staff SET status = 'suspended', suspension_reason = ?, suspended_by = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$reason, $suspended_by, $staffId]);
            $this->security->writeAuditLog($pdo, 'staff_suspended', $staff['staff_id'], 'staff', $adminId, 'admin', "Suspended staff: {$staff['full_name']}. Reason: {$reason}");
            return ['success' => true, 'message' => 'Staff member suspended successfully'];
        } elseif ($action === 'reinstate') {
            if ($staff['status'] !== 'suspended') {
                throw new ApiException('Staff is not suspended', 409);
            }
            $stmt = $pdo->prepare("UPDATE staff SET status = 'active', suspension_reason = NULL, suspended_by = NULL, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$staffId]);
            $this->security->writeAuditLog($pdo, 'staff_reinstated', $staff['staff_id'], 'staff', $adminId, 'admin', "Reinstated staff: {$staff['full_name']}");
            return ['success' => true, 'message' => 'Staff member reinstated successfully'];
        } elseif ($action === 'reset_password') {
            $temp_pass = $this->service->generateStrongPassword(12);
            $password_hash = password_hash($temp_pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE staff SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$password_hash, $staffId]);
            $this->security->writeAuditLog($pdo, 'staff_password_reset', $staff['staff_id'], 'staff', $adminId, 'admin', "Reset password for staff: {$staff['full_name']} ({$staff['email']})");
            return ['success' => true, 'message' => 'Temporary password generated successfully', 'temp_password' => $temp_pass];
        } elseif ($action === 'delete') {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
            $stmt->execute([$staffId]);
            $this->security->writeAuditLog($pdo, 'staff_deleted', $staff['staff_id'], 'staff', $adminId, 'admin', "Permanently deleted staff: {$staff['full_name']} ({$staff['email']})");
            $pdo->commit();
            return ['success' => true, 'message' => 'Staff member deleted permanently'];
        }

        return ['success' => false];
    }

    public function uploadStaffPhoto(): array
    {
        $staffId = intval($_POST['staff_id'] ?? 0);
        if (!$staffId) {
            throw new ApiException('Staff ID is required', 422);
        }

        $pdo = $this->service->getRepo()->getPDO();
        $stmt = $pdo->prepare("SELECT id, profile_photo FROM staff WHERE id = ?");
        $stmt->execute([$staffId]);
        $staff = $stmt->fetch();

        if (!$staff) {
            throw new ApiException('Staff member not found', 404);
        }
        if (\App\Shared\Core\Validation\Validator::isEmpty($_FILES['photo']['name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            throw new ApiException('Photo file is required', 422);
        }

        $file = $_FILES['photo'];
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new ApiException('Only JPG and PNG images are allowed', 422);
        }
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            throw new ApiException('File size exceeds 5MB limit', 422);
        }

        if ($staff['profile_photo']) {
            $old_path = dirname(__DIR__, 3) . '/' . $staff['profile_photo'];
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'staff_photo_' . $staffId . '_' . time() . '.' . $ext;
        $targetDir = UPLOAD_STAFF_PHOTOS_PATH;

        if (!move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
            throw new ApiException('Failed to save uploaded file', 500);
        }

        $new_photo_path = 'uploads/staff-photos/' . $filename;
        $stmt = $pdo->prepare("UPDATE staff SET profile_photo = ? WHERE id = ?");
        $stmt->execute([$new_photo_path, $staffId]);

        return ['success' => true, 'message' => 'Photo updated successfully', 'photo_url' => APP_URL . '/' . $new_photo_path];
    }

    public function staffDetail(): array
    {
        $staffId = intval($_GET['staff_id'] ?? $_GET['id'] ?? 0);
        if (!$staffId) {
            throw new ApiException('Staff ID is required', 422);
        }

        $pdo = $this->service->getRepo()->getPDO();

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
        $stmt->execute([$staffId]);
        $staff = $stmt->fetch();
        if (!$staff) {
            throw new ApiException('Staff member not found', 404);
        }

        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_transactions,
                COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
                COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals,
                COUNT(CASE WHEN type = 'deposit' THEN 1 END) as deposit_count,
                COUNT(CASE WHEN type = 'withdrawal' THEN 1 END) as withdrawal_count
            FROM transactions WHERE processed_by_staff_id = ? AND status = 'completed'
        ");
        $stmt->execute([$staffId]);
        $performance = $stmt->fetch();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_transactions,
                COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
                COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals,
                COUNT(CASE WHEN type = 'deposit' THEN 1 END) as deposit_count,
                COUNT(CASE WHEN type = 'withdrawal' THEN 1 END) as withdrawal_count
            FROM transactions WHERE processed_by_staff_id = ? AND status = 'completed' AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([$staffId]);
        $today_performance = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT DATE(created_at)) FROM transactions WHERE processed_by_staff_id = ?");
        $stmt->execute([$staffId]);
        $total_shifts = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT t.txn_id, t.type, t.amount, t.status, t.created_at,
                COALESCE(a.account_no, 'N/A') as account_no, COALESCE(u.full_name, 'N/A') as customer_name
            FROM transactions t
            LEFT JOIN accounts a ON COALESCE(t.to_account_id, t.from_account_id) = a.id
            LEFT JOIN users u ON a.user_id = u.customer_id
            WHERE t.processed_by_staff_id = ? ORDER BY t.created_at DESC LIMIT 20
        ");
        $stmt->execute([$staffId]);
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
        $stmt->execute(['id' => $staffId, 'actor_id' => $staff['staff_id']]);
        $shifts = $stmt->fetchAll();

        $documents = [];
        if (!\App\Shared\Core\Validation\Validator::isEmpty($staff['id_type'])) {
            $documents[] = ['id_type' => $staff['id_type'], 'id_number' => $staff['id_number'], 'front_image' => $staff['front_image'], 'back_image' => $staff['back_image']];
        }

        $address = ['street' => $staff['street'], 'city' => $staff['city'], 'state' => $staff['state'], 'pincode' => $staff['pincode'], 'country' => $staff['country']];

        $stmt = $pdo->prepare("SELECT id, action_type, entity_id, entity_type, details, ip_address, created_at FROM audit_log WHERE actor_id = ? AND actor_role IN ('Staff', 'staff') ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$staff['staff_id']]);
        $activities = $stmt->fetchAll();

        return [
            'success' => true, 'staff' => $staff, 'performance' => $performance, 'today_performance' => $today_performance,
            'total_shifts' => $total_shifts, 'shifts' => $shifts, 'transactions' => $transactions,
            'activities' => $activities, 'documents' => $documents, 'address' => $address
        ];
    }

    public function getStaffList(): array
    {
        $pdo = $this->service->getRepo()->getPDO();
        $search    = $this->security->cleanInput($_GET['search'] ?? '');
        $status    = $this->security->cleanInput($_GET['status'] ?? 'all');
        $filter    = $this->security->cleanInput($_GET['filter'] ?? '');
        $branchId = intval($_GET['branch_id'] ?? 0);
        $page      = max(1, intval($_GET['page'] ?? 1));
        $per_page  = 20;

        $where_clauses = ["1=1", "s.status != 'suspended'"];
        $params = [];
        if ($branchId > 0) {
            $where_clauses[] = "s.branch_id = ?";
            $params[] = $branchId;
        }
        if ($filter === 'unassigned') {
            $where_clauses[] = "s.branch_id IS NULL";
        }
        if (!\App\Shared\Core\Validation\Validator::isEmpty($search)) {
            $where_clauses[] = "(s.full_name LIKE ? OR s.personal_email LIKE ? OR s.staff_id LIKE ? OR s.phone LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
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
            if ($s['profile_photo']) {
                $s['profile_photo_url'] = $base_url . $s['profile_photo'];
            }
            $name_parts = explode(' ', $s['full_name']);
            $s['initials'] = strtoupper(substr($name_parts[0], 0, 1) . (\App\Shared\Core\Validation\Validator::isSet($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
        }

        return [
            'success' => true, 'staff' => $staff_list, 'total' => $total, 'pages' => $pages, 'current_page' => $page,
            'stats' => ['total_staff' => (int)$total_staff, 'active_staff' => (int)$active_staff, 'suspended_staff' => (int)$suspended_staff, 'transactions_today' => (int)$txns_today]
        ];
    }

    public function suspendedStaffDetail(): array
    {
        $staffId = intval($_GET['id'] ?? 0);
        if (!$staffId) {
            throw new ApiException('Staff ID is required', 422);
        }

        $pdo = $this->service->getRepo()->getPDO();
        $stmt = $pdo->prepare("
            SELECT s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone, s.designation as role, s.status,
                    s.suspension_reason, s.suspended_by, s.updated_at as suspended_at, s.created_at as onboarding_date, s.profile_photo, b.name as branch_name
            FROM staff s LEFT JOIN branches b ON s.branch_id = b.id WHERE s.id = ? AND s.status = 'suspended'
        ");
        $stmt->execute([$staffId]);
        $staff = $stmt->fetch();

        if (!$staff) {
            throw new ApiException('Suspended staff record not found', 404);
        }

        $name_parts = explode(' ', $staff['full_name']);
        $staff['initials'] = strtoupper(substr($name_parts[0], 0, 1) . (\App\Shared\Core\Validation\Validator::isSet($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
        $staff['suspended_at'] = date('d M Y, H:i', strtotime($staff['suspended_at']));
        $staff['onboarding_date'] = date('d M Y', strtotime($staff['onboarding_date']));
        $staff['duration'] = 'Indefinite';

        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_count, COALESCE(SUM(amount), 0) as total_volume,
                COUNT(CASE WHEN type = 'deposit' THEN 1 END) as deposit_count,
                COUNT(CASE WHEN type = 'withdrawal' THEN 1 END) as withdrawal_count
            FROM transactions WHERE processed_by_staff_id = ? AND status = 'completed'
        ");
        $stmt->execute([$staffId]);
        $stats = $stmt->fetch();

        $staff['lifetime_volume'] = '₹' . number_format($stats['total_volume'], 2);
        $staff['deposit_count'] = $stats['deposit_count'];
        $staff['withdrawal_count'] = $stats['withdrawal_count'];
        $staff['avg_daily_volume'] = '₹' . number_format($stats['total_volume'] / 30, 2);

        $stmt = $pdo->prepare("SELECT type, amount, status, created_at as timestamp FROM transactions WHERE processed_by_staff_id = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$staffId]);
        $history = $stmt->fetchAll();

        foreach ($history as &$h) {
            $h['amount'] = '₹' . number_format($h['amount'], 2);
            $h['timestamp'] = date('d M Y, H:i', strtotime($h['timestamp']));
            $h['type'] = strtoupper($h['type']);
            $h['status'] = strtoupper($h['status']);
        }

        return ['status' => 'success', 'data' => ['staff' => $staff, 'history' => $history]];
    }

    public function getSuspendedStaff(): array
    {
        $pdo = $this->service->getRepo()->getPDO();
        $search     = $this->security->cleanInput($_GET['search'] ?? '');
        $reason     = $this->security->cleanInput($_GET['reason'] ?? '');
        $start_date = $this->security->cleanInput($_GET['start_date'] ?? '');
        $end_date   = $this->security->cleanInput($_GET['end_date'] ?? '');
        $page       = max(1, intval($_GET['page'] ?? 1));
        $per_page   = min(50, max(10, intval($_GET['per_page'] ?? 20)));

        $where_clauses = ["s.status = 'suspended'"];
        $params = [];

        if (!\App\Shared\Core\Validation\Validator::isEmpty($search)) {
            $where_clauses[] = "(s.full_name LIKE ? OR s.personal_email LIKE ? OR s.staff_id LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }
        if (!\App\Shared\Core\Validation\Validator::isEmpty($reason)) {
            $where_clauses[] = "s.suspension_reason = ?";
            $params[] = $reason;
        }
        if (!\App\Shared\Core\Validation\Validator::isEmpty($start_date)) {
            $where_clauses[] = "s.updated_at >= ?";
            $params[] = $start_date . ' 00:00:00';
        }
        if (!\App\Shared\Core\Validation\Validator::isEmpty($end_date)) {
            $where_clauses[] = "s.updated_at <= ?";
            $params[] = $end_date . ' 23:59:59';
        }

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
            $s['initials'] = strtoupper(substr($name_parts[0], 0, 1) . (\App\Shared\Core\Validation\Validator::isSet($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
            $s['suspended_at'] = date('d M Y, H:i', strtotime($s['updated_at']));
            $s['suspension_reason'] = $s['suspension_reason'] ?: 'Administrative Review';
            $s['suspended_by'] = $s['suspended_by'] ?: 'Admin';
            $s['duration'] = 'Indefinite';
        }

        return ['status' => 'success', 'data' => ['staff' => $staff_list, 'total' => $total, 'pages' => $pages, 'current_page' => $page]];
    }
}
