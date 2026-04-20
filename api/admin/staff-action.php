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

if (!in_array($action, ['suspend', 'reinstate', 'reset_password'])) {
    json_response(['success' => false, 'message' => 'Invalid action'], 422);
}

$admin_id = $_SESSION['admin_id'];

try {
    $stmt = $pdo->prepare("SELECT id, staff_id, full_name, email, status FROM staff WHERE id = ?");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch();

    if (!$staff) {
        json_response(['success' => false, 'message' => 'Staff member not found'], 404);
    }

    if ($action === 'suspend') {
        if ($staff['status'] === 'suspended') {
            json_response(['success' => false, 'message' => 'Staff is already suspended'], 409);
        }

        $stmt = $pdo->prepare("UPDATE staff SET status = 'suspended', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$staff_id]);

        write_audit_log($pdo, 'staff_suspended', $staff['staff_id'], 'staff', $admin_id, 'admin',
            "Suspended staff: {$staff['full_name']}. Reason: {$reason}");

        json_response(['success' => true, 'message' => 'Staff member suspended successfully']);

    } elseif ($action === 'reinstate') {
        if ($staff['status'] !== 'suspended') {
            json_response(['success' => false, 'message' => 'Staff is not suspended'], 409);
        }

        $stmt = $pdo->prepare("UPDATE staff SET status = 'active', updated_at = NOW() WHERE id = ?");
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
    }

} catch (PDOException $e) {
    error_log("Staff action error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Action failed. Please try again.'], 500);
}
