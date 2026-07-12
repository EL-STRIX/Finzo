<?php
namespace App\Controllers\Admin;

use App\Services\AdminStaffBranchService;
use Exception;
use PDOException;
use PDO;

trait AdminStaffBranchControllerTrait1 {
    public function deleteBranch() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';
        if (!validate_csrf_token($csrf)) json_response(['success' => false, 'message' => 'Invalid security token'], 403);
        
        $admin_id = $_SESSION['admin_id'];
        $branch_id = intval($_POST['branch_id'] ?? 0);
        $admin_password = $_POST['admin_password'] ?? '';
        $confirm_name = $_POST['confirm_name'] ?? '';

        if (empty($branch_id) || empty($admin_password) || empty($confirm_name)) json_response(['success' => false, 'message' => 'Missing required fields'], 422);

        try {
            $repo = $this->service->getRepo();
            $pdo = $repo->getPDO();
            
            $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
            $stmt->execute([$admin_id]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($admin_password, $admin['password_hash'])) json_response(['success' => false, 'message' => 'Invalid admin password'], 401);

            $stmt = $pdo->prepare("SELECT id, name, type FROM branches WHERE id = ?");
            $stmt->execute([$branch_id]);
            $branch = $stmt->fetch();

            if (!$branch) json_response(['success' => false, 'message' => 'Branch not found'], 404);
            if ($branch['type'] === 'system') json_response(['success' => false, 'message' => 'System branches cannot be deleted'], 403);
            if (strtolower(trim($branch['name'])) !== strtolower(trim($confirm_name))) json_response(['success' => false, 'message' => 'Branch name confirmation does not match'], 400);

            $pdo->beginTransaction();

            if (isset($_FILES['closure_document']) && $_FILES['closure_document']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['closure_document']['name'], PATHINFO_EXTENSION);
                $filename = 'closure_doc_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                $dest_dir = dirname(__DIR__, 3) . '/uploads/branch-certs/';
                if (!is_dir($dest_dir)) mkdir($dest_dir, 0755, true);
                move_uploaded_file($_FILES['closure_document']['tmp_name'], $dest_dir . $filename);
            }

            $stmt = $pdo->prepare("UPDATE staff SET branch_id = NULL WHERE branch_id = ?");
            $stmt->execute([$branch_id]);

            $stmt = $pdo->prepare("DELETE FROM branches WHERE id = ?");
            $stmt->execute([$branch_id]);

            write_audit_log($pdo, 'branch_deleted', $branch['name'], 'branch', $admin_id, 'admin', "Deleted branch: {$branch['name']}");

            $pdo->commit();
            json_response(['success' => true, 'message' => 'Branch deleted successfully']);
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Database error occurred'], 500);
        }
    }

    public function generateStaffPassword() {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';
        if (empty($csrf) || $csrf !== $_SESSION['csrf_token']) json_response(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        try {
            json_response(['success' => true, 'password' => $this->service->generateStrongPassword(12)]);
        } catch (Exception $e) {
            json_response(['success' => false, 'message' => 'Error generating password'], 500);
        }
    }

    public function getNextStaffId() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        try {
            json_response(['success' => true, 'next_id' => $this->service->generateStaffId()]);
        } catch (PDOException $e) {
            json_response(['success' => false, 'message' => 'Failed to calculate next ID'], 500);
        }
    }

    public function resetStaffPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        $admin_id = $_SESSION['admin_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';
        if (!validate_csrf_token($csrf)) json_response(['success' => false, 'message' => 'Invalid CSRF token'], 403);

        $pdo = $this->service->getRepo()->getPDO();
        check_admin_permission($pdo, $admin_id, 'reset_staff_password');

        $staff_id = intval($input['staff_id'] ?? $_POST['staff_id'] ?? 0);
        $new_password = $input['new_password'] ?? $_POST['new_password'] ?? '';

        if (!$staff_id) json_response(['success' => false, 'message' => 'Staff ID is required'], 422);
        if (strlen($new_password) < 8) json_response(['success' => false, 'message' => 'Password must be at least 8 characters'], 422);

        try {
            $stmt = $pdo->prepare("SELECT id, full_name, staff_id FROM staff WHERE id = ?");
            $stmt->execute([$staff_id]);
            $staff = $stmt->fetch();

            if (!$staff) json_response(['success' => false, 'message' => 'Staff member not found'], 404);

            $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE staff SET password_hash = ? WHERE id = ?");
            $stmt->execute([$password_hash, $staff_id]);

            write_audit_log($pdo, 'staff_password_reset', $staff_id, 'staff', $admin_id, 'admin', "Password reset for staff: " . $staff['full_name'] . " (" . $staff['staff_id'] . ")");
            json_response(['success' => true, 'message' => 'Password updated successfully']);
        } catch (PDOException $e) {
            json_response(['success' => false, 'message' => 'Failed to reset password'], 500);
        }
    }

    public function staffAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        $staff_id = intval($_POST['staff_id'] ?? 0);
        $action   = clean_input($_POST['action'] ?? '');
        $reason   = clean_input($_POST['reason'] ?? '');
        
        if (!$staff_id || empty($action)) json_response(['success' => false, 'message' => 'Staff ID and action are required'], 422);
        if (!in_array($action, ['suspend', 'reinstate', 'reset_password', 'delete'])) json_response(['success' => false, 'message' => 'Invalid action'], 422);
        
        $admin_id = $_SESSION['admin_id'];
        $password = $_POST['password'] ?? '';
        if (empty($password)) json_response(['success' => false, 'message' => 'Admin password is required for this action'], 422);
        
        $pdo = $this->service->getRepo()->getPDO();
        if (in_array($action, ['suspend', 'reinstate', 'delete'])) check_admin_permission($pdo, $admin_id, 'suspend_staff');

        try {
            $admin_stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
            $admin_stmt->execute([$admin_id]);
            $admin = $admin_stmt->fetch();
            if (!$admin || !password_verify($password, $admin['password_hash'])) json_response(['success' => false, 'message' => 'Invalid administrative password. Access denied.'], 403);

            $stmt = $pdo->prepare("SELECT id, staff_id, full_name, personal_email as email, status FROM staff WHERE id = ?");
            $stmt->execute([$staff_id]);
            $staff = $stmt->fetch();
            if (!$staff) json_response(['success' => false, 'message' => 'Staff member not found'], 404);

            if ($action === 'suspend') {
                if ($staff['status'] === 'suspended') json_response(['success' => false, 'message' => 'Staff is already suspended'], 409);
                $suspended_by = $_SESSION['admin_name'] ?? 'Admin';
                $stmt = $pdo->prepare("UPDATE staff SET status = 'suspended', suspension_reason = ?, suspended_by = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$reason, $suspended_by, $staff_id]);
                write_audit_log($pdo, 'staff_suspended', $staff['staff_id'], 'staff', $admin_id, 'admin', "Suspended staff: {$staff['full_name']}. Reason: {$reason}");
                json_response(['success' => true, 'message' => 'Staff member suspended successfully']);
            } elseif ($action === 'reinstate') {
                if ($staff['status'] !== 'suspended') json_response(['success' => false, 'message' => 'Staff is not suspended'], 409);
                $stmt = $pdo->prepare("UPDATE staff SET status = 'active', suspension_reason = NULL, suspended_by = NULL, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$staff_id]);
                write_audit_log($pdo, 'staff_reinstated', $staff['staff_id'], 'staff', $admin_id, 'admin', "Reinstated staff: {$staff['full_name']}");
                json_response(['success' => true, 'message' => 'Staff member reinstated successfully']);
            } elseif ($action === 'reset_password') {
                $temp_pass = $this->service->generateStrongPassword(12);
                $password_hash = password_hash($temp_pass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE staff SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$password_hash, $staff_id]);
                write_audit_log($pdo, 'staff_password_reset', $staff['staff_id'], 'staff', $admin_id, 'admin', "Reset password for staff: {$staff['full_name']} ({$staff['email']})");
                json_response(['success' => true, 'message' => 'Temporary password generated successfully', 'temp_password' => $temp_pass]);
            } elseif ($action === 'delete') {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
                $stmt->execute([$staff_id]);
                write_audit_log($pdo, 'staff_deleted', $staff['staff_id'], 'staff', $admin_id, 'admin', "Permanently deleted staff: {$staff['full_name']} ({$staff['email']})");
                $pdo->commit();
                json_response(['success' => true, 'message' => 'Staff member deleted permanently']);
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Action failed: ' . $e->getMessage()], 500);
        }
    }
}
