<?php
/**
 * Finzo Banking System — Reset Password & Unlock
 * POST endpoint: Forced password reset after successful security answer.
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// 1. Validate CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrf_token)) {
    json_response(['success' => false, 'message' => 'Invalid CSRF token'], 403);
}

// 2. Validate inputs
$reset_token = $_POST['reset_token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($reset_token)) {
    json_response(['success' => false, 'message' => 'Reset token is required'], 422);
}

if (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[!@#$%^&*]/', $new_password)) {
    json_response(['success' => false, 'message' => 'Password does not meet requirements'], 422);
}

if ($new_password !== $confirm_password) {
    json_response(['success' => false, 'message' => 'Passwords do not match'], 422);
}

// 3. Query
$stmt = $pdo->prepare("
    SELECT id, full_name, password_reset_expires_at
    FROM admin_users
    WHERE password_reset_token = ? AND password_reset_expires_at > NOW()
");
$stmt->execute([$reset_token]);
$admin = $stmt->fetch();

// 4. If not found or expired
if (!$admin) {
    json_response([
        'success' => false,
        'message' => 'Reset link is invalid or expired. Please restart the unlock process.'
    ], 400);
}

// 5. Hash new_password
$new_hash = password_hash($new_password, PASSWORD_BCRYPT);

// 6. Update admin_users to unlock account
$updateStmt = $pdo->prepare("
    UPDATE admin_users 
    SET password_hash = ?, 
        account_locked = 0, 
        failed_attempts = 0, 
        answer_attempts = 0, 
        answer_locked_until = NULL, 
        locked_at = NULL, 
        lock_reason = NULL, 
        password_reset_token = NULL, 
        password_reset_expires_at = NULL
    WHERE id = ?
");
$updateStmt->execute([$new_hash, $admin['id']]);

// 7. Insert admin_lock_log
$logStmt = $pdo->prepare("
    INSERT INTO admin_lock_log (admin_id, event_type, details) 
    VALUES (?, 'password_reset_completed', 'Password reset after security question unlock')
");
$logStmt->execute([$admin['id']]);

// 8. Write audit log
write_audit_log($pdo, 'admin_password_reset_unlock', $admin['id'], 'admin', $admin['id'], 'admin', 'Account fully unlocked after password reset');

// 9. Return
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
json_response([
    'success' => true,
    'message' => 'Password updated. You can now login.',
    'redirect' => $base . '/views/auth/admin-login.html'
]);
