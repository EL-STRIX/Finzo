<?php
/**
 * api/admin/reset-staff-password.php
 * POST endpoint: Admin resets a staff member's password.
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$admin_id = $_SESSION['admin_id'];

// 1. Validate CSRF token
$input = json_decode(file_get_contents('php://input'), true);
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';

if (!validate_csrf_token($csrf_token)) {
    json_response(['success' => false, 'message' => 'Invalid CSRF token'], 403);
}

// 2. Check admin permission
check_admin_permission($pdo, $admin_id, 'reset_staff_password');

$staff_id     = intval($input['staff_id'] ?? $_POST['staff_id'] ?? 0);
$new_password = $input['new_password'] ?? $_POST['new_password'] ?? '';

if (!$staff_id) {
    json_response(['success' => false, 'message' => 'Staff ID is required'], 422);
}

if (strlen($new_password) < 8) {
    json_response(['success' => false, 'message' => 'Password must be at least 8 characters'], 422);
}

try {
    // Check if staff exists
    $stmt = $pdo->prepare("SELECT id, full_name, staff_id FROM staff WHERE id = ?");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch();

    if (!$staff) {
        json_response(['success' => false, 'message' => 'Staff member not found'], 404);
    }

    // 4. Hash new password
    $password_hash = password_hash($new_password, PASSWORD_BCRYPT);

    // 5. Update password
    $stmt = $pdo->prepare("UPDATE staff SET password_hash = ? WHERE id = ?");
    $stmt->execute([$password_hash, $staff_id]);

    // 6. Audit Log
    write_audit_log($pdo, 'staff_password_reset', $staff_id, 'staff', $admin_id, 'admin', 
        "Password reset for staff: " . $staff['full_name'] . " (" . $staff['staff_id'] . ")");

    json_response([
        'success' => true,
        'message' => 'Password updated successfully'
    ]);

} catch (PDOException $e) {
    error_log("Staff password reset error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to reset password'], 500);
}
