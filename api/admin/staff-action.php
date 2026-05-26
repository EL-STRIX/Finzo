<?php
/**
 * Finzo Banking System — Admin Staff Action API
 * POST endpoint: suspend or reinstate a staff member.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$staff_id = intval($_POST['staff_id'] ?? 0);
$action   = sanitize_input($_POST['action'] ?? '');
$reason   = sanitize_input($_POST['reason'] ?? '');

if (!$staff_id || empty($action)) {
    json_response(['success' => false, 'message' => 'Staff ID and action are required'], 422);
}

if (!in_array($action, ['suspend', 'reinstate', 'reset_password', 'delete'])) {
    json_response(['success' => false, 'message' => 'Invalid action'], 422);
}

$admin_id = $_SESSION['admin_id'];
$password = $_POST['password'] ?? '';

if (empty($password)) {
    json_response(['success' => false, 'message' => 'Admin password is required for this action'], 422);
}

// Prevent restricted admins from suspending or reinstating staff
if (in_array($action, ['suspend', 'reinstate', 'delete'])) {
    check_admin_permission($pdo, $admin_id, 'suspend_staff');
}

try {
    // 1. Verify Admin Password
    $admin_stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
    $admin_stmt->execute([$admin_id]);
    $admin = $admin_stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        json_response(['success' => false, 'message' => 'Invalid administrative password. Access denied.'], 403);
    }

    // 2. Fetch Staff
    $stmt = $pdo->prepare("SELECT id, staff_id, full_name, personal_email as email, status FROM staff WHERE id = ?");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch();

    if (!$staff) {
        json_response(['success' => false, 'message' => 'Staff member not found'], 404);
    }

    if ($action === 'suspend') {
        if ($staff['status'] === 'suspended') {
            json_response(['success' => false, 'message' => 'Staff is already suspended'], 409);
        }

        $suspended_by = $_SESSION['admin_name'] ?? 'Admin';
        $stmt = $pdo->prepare("UPDATE staff SET status = 'suspended', suspension_reason = ?, suspended_by = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$reason, $suspended_by, $staff_id]);

        write_audit_log($pdo, 'staff_suspended', $staff['staff_id'], 'staff', $admin_id, 'admin',
            "Suspended staff: {$staff['full_name']}. Reason: {$reason}");

        json_response(['success' => true, 'message' => 'Staff member suspended successfully']);

    } elseif ($action === 'reinstate') {
        if ($staff['status'] !== 'suspended') {
            json_response(['success' => false, 'message' => 'Staff is not suspended'], 409);
        }

        $stmt = $pdo->prepare("UPDATE staff SET status = 'active', suspension_reason = NULL, suspended_by = NULL, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$staff_id]);

        write_audit_log($pdo, 'staff_reinstated', $staff['staff_id'], 'staff', $admin_id, 'admin',
            "Reinstated staff: {$staff['full_name']}");

        json_response(['success' => true, 'message' => 'Staff member reinstated successfully']);
    } elseif ($action === 'reset_password') {
        // Generate secure temp password
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#$%!';
        $temp_pass = '';
        $temp_pass .= 'abcdefghjkmnpqrstuvwxyz'[rand(0, 23)];
        $temp_pass .= 'ABCDEFGHJKMNPQRSTUVWXYZ'[rand(0, 23)];
        $temp_pass .= '23456789'[rand(0, 7)];
        $temp_pass .= '@#$%!'[rand(0, 4)];
        for ($i = 4; $i < 12; $i++) {
            $temp_pass .= $chars[rand(0, strlen($chars) - 1)];
        }
        $temp_pass = str_shuffle($temp_pass);

        $password_hash = password_hash($temp_pass, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("UPDATE staff SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$password_hash, $staff_id]);

        write_audit_log($pdo, 'staff_password_reset', $staff['staff_id'], 'staff', $admin_id, 'admin',
            "Reset password for staff: {$staff['full_name']} ({$staff['email']})");

        json_response([
            'success' => true,
            'message' => 'Temporary password generated successfully',
            'temp_password' => $temp_pass
        ]);
    } elseif ($action === 'delete') {
        $pdo->beginTransaction();
        
        // Delete from staff (Address & Documents are consolidated inside staff)
        $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->execute([$staff_id]);

        write_audit_log($pdo, 'staff_deleted', $staff['staff_id'], 'staff', $admin_id, 'admin',
            "Permanently deleted staff: {$staff['full_name']} ({$staff['email']})");

        $pdo->commit();
        json_response(['success' => true, 'message' => 'Staff member deleted permanently']);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Staff action error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Action failed: ' . $e->getMessage()], 500);
}
