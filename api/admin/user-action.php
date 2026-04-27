<?php
/**
 * Finzo Banking System — Admin User Action API
 * POST endpoint: suspend, reinstate, or soft-delete a customer account.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$user_id = intval($_POST['user_id'] ?? 0);
$action  = sanitize_input($_POST['action'] ?? '');
$reason  = sanitize_input($_POST['reason'] ?? '');

if (!$user_id || empty($action)) {
    json_response(['success' => false, 'message' => 'User ID and action are required'], 422);
}

$valid_actions = ['suspend', 'reinstate', 'delete', 'recover'];
if (!in_array($action, $valid_actions)) {
    json_response(['success' => false, 'message' => 'Invalid action'], 422);
}

$admin_id = $_SESSION['admin_id'];

try {
    // Verify user exists
    $stmt = $pdo->prepare("SELECT id, full_name, email, status, is_deleted FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }

    switch ($action) {
        case 'suspend':
            check_admin_permission($pdo, $admin_id, 'suspend_user');
            if ($user['status'] === 'suspended') {
                json_response(['success' => false, 'message' => 'User is already suspended'], 409);
            }

            $stmt = $pdo->prepare("UPDATE users SET status = 'suspended', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);

            // Also suspend the account
            $stmt = $pdo->prepare("UPDATE accounts SET status = 'suspended', updated_at = NOW() WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Notification
            send_notification($pdo, $user_id, 'security', 'Account Suspended',
                'Your account has been suspended. Reason: ' . ($reason ?: 'No reason provided') . '. Contact support for assistance.');

            write_audit_log($pdo, 'user_suspended', $user_id, 'user', $admin_id, 'admin',
                "User {$user['full_name']} suspended. Reason: {$reason}");

            json_response(['success' => true, 'message' => 'User suspended successfully']);
            break;

        case 'reinstate':
            check_admin_permission($pdo, $admin_id, 'reinstate_user');
            if ($user['status'] !== 'suspended') {
                json_response(['success' => false, 'message' => 'User is not suspended'], 409);
            }

            $stmt = $pdo->prepare("UPDATE users SET status = 'active', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);

            $stmt = $pdo->prepare("UPDATE accounts SET status = 'active', updated_at = NOW() WHERE user_id = ? AND status = 'suspended'");
            $stmt->execute([$user_id]);

            send_notification($pdo, $user_id, 'security', 'Account Reinstated',
                'Your account has been reinstated and is now active. You can log in and transact normally.');

            write_audit_log($pdo, 'user_reinstated', $user_id, 'user', $admin_id, 'admin',
                "User {$user['full_name']} reinstated");

            json_response(['success' => true, 'message' => 'User reinstated successfully']);
            break;

        case 'delete':
            check_admin_permission($pdo, $admin_id, 'soft_delete_user');
            if ($user['is_deleted']) {
                json_response(['success' => false, 'message' => 'User is already deleted'], 409);
            }

            // Soft delete
            $stmt = $pdo->prepare("UPDATE users SET is_deleted = 1, deleted_at = NOW(), status = 'suspended', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);

            $stmt = $pdo->prepare("UPDATE accounts SET status = 'closed', updated_at = NOW() WHERE user_id = ?");
            $stmt->execute([$user_id]);

            write_audit_log($pdo, 'user_deleted', $user_id, 'user', $admin_id, 'admin',
                "User {$user['full_name']} soft-deleted. Reason: {$reason}");

            json_response(['success' => true, 'message' => 'User deleted successfully']);
            break;

        case 'recover':
            check_admin_permission($pdo, $admin_id, 'soft_delete_user');
            if (!$user['is_deleted']) {
                json_response(['success' => false, 'message' => 'User is not deleted'], 409);
            }

            // Restore from soft-delete
            $stmt = $pdo->prepare("UPDATE users SET is_deleted = 0, deleted_at = NULL, status = 'active', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);

            // Reactivate account
            $stmt = $pdo->prepare("UPDATE accounts SET status = 'active', updated_at = NOW() WHERE user_id = ?");
            $stmt->execute([$user_id]);

            write_audit_log($pdo, 'user_recovered', $user_id, 'user', $admin_id, 'admin',
                "User {$user['full_name']} recovered from soft-delete state.");

            json_response(['success' => true, 'message' => 'User recovered successfully']);
            break;
    }

} catch (PDOException $e) {
    error_log("Admin user action error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Action failed. Please try again.'], 500);
}
