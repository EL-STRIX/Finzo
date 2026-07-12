<?php
/**
 * Finzo Banking System — Admin Recover Deleted User API
 * POST endpoint: restores a soft-deleted customer account.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$user_id = intval($_POST['user_id'] ?? 0);
if (!$user_id) {
    json_response(['success' => false, 'message' => 'User ID is required'], 422);
}

$admin_id = $_SESSION['admin_id'];

try {
    // Verify user is deleted
    $stmt = $pdo->prepare("SELECT id, customer_id, full_name, is_deleted FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }

    if (!$user['is_deleted']) {
        json_response(['success' => false, 'message' => 'User is not deleted'], 409);
    }

    // Restore user
    $stmt = $pdo->prepare("
        UPDATE users SET is_deleted = 0, deleted_at = NULL, status = 'active', updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);

    // Restore account
    $stmt = $pdo->prepare("
        UPDATE accounts SET status = 'active', updated_at = NOW()
        WHERE user_id = ? AND status = 'closed'
    ");
    $stmt->execute([$user['customer_id']]);

    // Notification
    send_notification($pdo, $user_id, 'system', 'Account Restored',
        'Your account has been restored and is now active. You can log in and continue banking.');

    // Audit log
    write_audit_log($pdo, 'user_recovered', $user_id, 'user', $admin_id, 'admin',
        "Recovered deleted user: {$user['full_name']}");

    json_response(['success' => true, 'message' => 'User recovered successfully']);

} catch (PDOException $e) {
    error_log("Recover user error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to recover user'], 500);
}
