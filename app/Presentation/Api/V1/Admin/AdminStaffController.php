<?php

namespace App\Presentation\Api\V1\Admin;

use App\Shared\Security\SecurityService;

use App\Application\Services\AdminStaffBranchService;
use App\Shared\Core\Exceptions\ApiException;
use PDOException;
use Exception;

class AdminStaffController
{
    protected SecurityService $security;

    protected AdminStaffBranchService $service;

    public function __construct(\App\Domain\Contracts\Services\AdminStaffBranchServiceInterface $service, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security;
        $this->service = $service;
    }



    public function addStaff()
    {

        $adminId = \App\Shared\Security\Auth::user()['id'];

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

    public function getStaffDetail(?string $id = null, ?string $extra = null): array
    {
        $staffId = intval($_GET['staff_id'] ?? ($id ?? $_GET['id'] ?? null) ?? 0);
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

    public function generateStaffPassword(?string $id = null, ?string $extra = null): array
    {

        return ['success' => true, 'password' => $this->service->generateStrongPassword(12)];
    }

    public function getNextStaffId(?string $id = null, ?string $extra = null): array
    {
        return ['success' => true, 'next_id' => $this->service->generateStaffId()];
    }

    public function resetStaffPassword(?string $id = null, ?string $extra = null): array
    {
        $adminId = \App\Shared\Security\Auth::user()['id'];
        $input = json_decode(file_get_contents('php://input'), true);


        $this->security->checkAdminPermission($adminId, 'reset_staff_password');

        $staffId = intval($input['staff_id'] ?? ($id ?? $_POST['staff_id'] ?? null) ?? 0);
        $newPassword = $input['new_password'] ?? $_POST['new_password'] ?? '';

        if (!$staffId) {
            throw new ApiException('Staff ID is required', 422);
        }
        if (\App\Shared\Core\Validation\Validator::length($newPassword) < 8) {
            throw new ApiException('Password must be at least 8 characters', 422);
        }

        $pdo = $this->service->getRepo()->getPDO();
        $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_4623d5b9'));
        $stmt->execute([$staffId]);
        $staff = $stmt->fetch();

        if (!$staff) {
            throw new ApiException('Staff member not found', 404);
        }

        $password_hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_a4dee608'));
        $stmt->execute([$password_hash, $staffId]);

        $this->security->writeAuditLog($pdo, 'staff_password_reset', $staffId, 'staff', $adminId, 'admin', "Password reset for staff: " . $staff['full_name'] . " (" . $staff['staff_id'] . ")");
        return ['success' => true, 'message' => 'Password updated successfully'];
    }

    public function staffAction(?string $id = null, ?string $extra = null): array
    {
        $staffId = intval(($id ?? $_POST['staff_id'] ?? null) ?? 0);
        $action   = $this->security->cleanInput($_POST['action'] ?? '');
        $reason   = $this->security->cleanInput($_POST['reason'] ?? '');

        if (!$staffId || \App\Shared\Core\Validation\Validator::isEmpty($action)) {
            throw new ApiException('Staff ID and action are required', 422);
        }
        if (!in_array($action, ['suspend', 'reinstate', 'reset_password', 'delete'])) {
            throw new ApiException('Invalid action', 422);
        }

        $adminId = \App\Shared\Security\Auth::user()['id'];
        $password = $_POST['password'] ?? '';
        if (\App\Shared\Core\Validation\Validator::isEmpty($password)) {
            throw new ApiException('Admin password is required for this action', 422);
        }

        $pdo = $this->service->getRepo()->getPDO();
        if (in_array($action, ['suspend', 'reinstate', 'delete'])) {
            $this->security->checkAdminPermission($adminId, 'suspend_staff');
        }

        $admin_stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_271aedbc'));
        $admin_stmt->execute([$adminId]);
        $admin = $admin_stmt->fetch();
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            throw new ApiException('Invalid administrative password. Access denied.', 403);
        }

        $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_109dc119'));
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
            $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_6013aa56'));
            $stmt->execute([$reason, $suspended_by, $staffId]);
            $this->security->writeAuditLog($pdo, 'staff_suspended', $staff['staff_id'], 'staff', $adminId, 'admin', "Suspended staff: {$staff['full_name']}. Reason: {$reason}");
            return ['success' => true, 'message' => 'Staff member suspended successfully'];
        } elseif ($action === 'reinstate') {
            if ($staff['status'] !== 'suspended') {
                throw new ApiException('Staff is not suspended', 409);
            }
            $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_953d62ef'));
            $stmt->execute([$staffId]);
            $this->security->writeAuditLog($pdo, 'staff_reinstated', $staff['staff_id'], 'staff', $adminId, 'admin', "Reinstated staff: {$staff['full_name']}");
            return ['success' => true, 'message' => 'Staff member reinstated successfully'];
        } elseif ($action === 'reset_password') {
            $temp_pass = $this->service->generateStrongPassword(12);
            $password_hash = password_hash($temp_pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_0bfb3963'));
            $stmt->execute([$password_hash, $staffId]);
            $this->security->writeAuditLog($pdo, 'staff_password_reset', $staff['staff_id'], 'staff', $adminId, 'admin', "Reset password for staff: {$staff['full_name']} ({$staff['email']})");
            return ['success' => true, 'message' => 'Temporary password generated successfully', 'temp_password' => $temp_pass];
        } elseif ($action === 'delete') {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_42144d21'));
            $stmt->execute([$staffId]);
            $this->security->writeAuditLog($pdo, 'staff_deleted', $staff['staff_id'], 'staff', $adminId, 'admin', "Permanently deleted staff: {$staff['full_name']} ({$staff['email']})");
            $pdo->commit();
            return ['success' => true, 'message' => 'Staff member deleted permanently'];
        }

        return ['success' => false];
    }

    public function uploadStaffPhoto(?string $id = null, ?string $extra = null): array
    {
        $staffId = intval(($id ?? $_POST['staff_id'] ?? null) ?? 0);
        if (!$staffId) {
            throw new ApiException('Staff ID is required', 422);
        }

        $pdo = $this->service->getRepo()->getPDO();
        $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_960c6e0a'));
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
        $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_28c17e7e'));
        $stmt->execute([$new_photo_path, $staffId]);

        return ['success' => true, 'message' => 'Photo updated successfully', 'photo_url' => APP_URL . '/' . $new_photo_path];
    }



    public function getStaffList(?string $id = null, ?string $extra = null): array
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
        $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_9f511115'));
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $pages  = max(1, ceil($total / $per_page));
        $offset = ($page - 1) * $per_page;

        $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_9199e7d3'));
        $stmt->execute($params);
        $staff_list = $stmt->fetchAll();

        $total_staff = $pdo->query(\App\Infrastructure\Repositories\QueryStore::get('q_94aa2694'))->fetchColumn();
        $active_staff = $pdo->query(\App\Infrastructure\Repositories\QueryStore::get('q_6ad2c8a1'))->fetchColumn();
        $suspended_staff = $pdo->query(\App\Infrastructure\Repositories\QueryStore::get('q_9dda4608'))->fetchColumn();
        $txns_today = $pdo->query(\App\Infrastructure\Repositories\QueryStore::get('q_db6f4d23'))->fetchColumn();

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

    public function getSuspendedStaff(?string $id = null, ?string $extra = null): array
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
        $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_9f511115'));
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $pages  = max(1, ceil($total / $per_page));
        $offset = ($page - 1) * $per_page;

        $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_011f9128'));
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

