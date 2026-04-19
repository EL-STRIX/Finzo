<?php
/**
 * Finzo Banking System — Admin Unlock Account API
 * POST endpoint: manually unlock a locked customer account.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$user_id  = intval($_POST['user_id'] ?? 0);
$admin_id = $_SESSION['admin_id'];

if (!$user_id) {
    json_response(['success' => false, 'message' => 'User ID is required'], 422);
}

try {
    // Generate a secure 12-character temporary password
    $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#$%!';
    $temp_pass = '';
    // Ensure at least one of each class
    $temp_pass .= 'abcdefghjkmnpqrstuvwxyz'[rand(0, 23)];
    $temp_pass .= 'ABCDEFGHJKMNPQRSTUVWXYZ'[rand(0, 23)];
    $temp_pass .= '23456789'[rand(0, 7)];
    $temp_pass .= '@#$%!'[rand(0, 4)];
    for ($i = 4; $i < 12; $i++) {
        $temp_pass .= $chars[rand(0, strlen($chars) - 1)];
    }
    $temp_pass = str_shuffle($temp_pass);

    // Verify user exists and is locked
    $stmt = $pdo->prepare("SELECT id, full_name, email, failed_attempts, locked_until FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }

    // Hash the temporary password
    $password_hash = password_hash($temp_pass, PASSWORD_BCRYPT);

    // Unlock the account and update password
    $stmt = $pdo->prepare("
        UPDATE users 
        SET failed_attempts = 0, 
            locked_until = NULL, 
            password_hash = ?, 
            updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$password_hash, $user_id]);

    // Notification for user
    send_notification($pdo, $user_id, 'security', 'Account Unlocked',
        'Your account has been unlocked by an administrator. Your password has been reset to a temporary one. Please check with an admin if you did not request this.');

    // Audit log
    write_audit_log($pdo, 'account_unlocked', $user_id, 'user', $admin_id, 'admin',
        "Manually unlocked account and reset password for {$user['full_name']} ({$user['email']}). Previous attempts: {$user['failed_attempts']}");

    json_response([
        'success' => true, 
        'message' => 'Account unlocked and temporary password generated.',
        'temp_password' => $temp_pass
    ]);

} catch (PDOException $e) {
    error_log("Unlock account error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to unlock account'], 500);
}
