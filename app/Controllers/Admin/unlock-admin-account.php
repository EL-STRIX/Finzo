<?php
/**
 * Finzo Banking System — Unlock Admin Account
 * POST endpoint: Super Admin manually unlocks any locked admin.
 */

require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__, 3) . '/config/functions.php';

// Check auth middleware (this should ideally be required but we can include it directly or ensure auth-admin.php is included if it exists in the middleware folder)
require_once dirname(__DIR__, 3) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// 1. Validate CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrf_token)) {
    json_response(['success' => false, 'message' => 'Invalid CSRF token'], 403);
}

// 2. Check super_admin role
if ($_SESSION['admin_role'] !== 'super_admin') {
    json_response(['success' => false, 'message' => 'Unauthorized. Super Admin access required.'], 403);
}

// 3. Inputs
$target_admin_id = (int)($_POST['target_admin_id'] ?? 0);

if ($target_admin_id <= 0) {
    json_response(['success' => false, 'message' => 'Target admin ID is required'], 422);
}

// Cannot unlock self through this endpoint
if ($target_admin_id === (int)$_SESSION['admin_id']) {
    json_response(['success' => false, 'message' => 'You cannot unlock your own account from here.'], 400);
}

// 4. Query target admin
$stmt = $pdo->prepare("SELECT id, full_name, account_locked, status FROM admin_users WHERE id = ?");
$stmt->execute([$target_admin_id]);
$target_admin = $stmt->fetch();

// 5. If not found
if (!$target_admin) {
    json_response(['success' => false, 'message' => 'Admin not found'], 404);
}

// 6. If account_locked = 0
if ($target_admin['account_locked'] == 0) {
    json_response(['success' => false, 'message' => 'This account is not locked'], 400);
}

// 7. Update admin_users
$updateStmt = $pdo->prepare("
    UPDATE admin_users 
    SET account_locked = 0, 
        failed_attempts = 0, 
        answer_attempts = 0, 
        answer_locked_until = NULL, 
        locked_at = NULL, 
        lock_reason = NULL, 
        password_reset_token = NULL, 
        password_reset_expires_at = NULL
    WHERE id = ?
");
$updateStmt->execute([$target_admin_id]);

// 8. Insert admin_lock_log
$logStmt = $pdo->prepare("
    INSERT INTO admin_lock_log (admin_id, event_type, triggered_by, details) 
    SELECT t.admin_id, 'unlocked_by_super_admin', tr.admin_id, 'Manually unlocked by Super Admin'
    FROM admin_users t, admin_users tr
    WHERE t.id = ? AND tr.id = ?
");
$logStmt->execute([$target_admin_id, $_SESSION['admin_id']]);

// 9. Write audit log
write_audit_log($pdo, 'admin_manually_unlocked', $target_admin_id, 'admin', $_SESSION['admin_id'], 'admin', "Super Admin manually unlocked: {$target_admin['full_name']}");

// 10. Return
json_response([
    'success' => true,
    'message' => "{$target_admin['full_name']} account has been unlocked. They can now login normally."
]);
