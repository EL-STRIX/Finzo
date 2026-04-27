<?php
/**
 * Finzo Banking System — Admin Action API
 * POST endpoint: suspend, activate, or delete an admin account.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid or expired session token'], 403);
}

if ($_SESSION['admin_role'] !== 'super_admin') {
    json_response(['success' => false, 'message' => 'Forbidden'], 403);
}

$target_admin_id = intval($_POST['target_admin_id'] ?? 0);
$action = sanitize_input($_POST['action'] ?? '');

if (!$target_admin_id || empty($action)) {
    json_response(['success' => false, 'message' => 'Missing target ID or action'], 422);
}

// Cannot target self
if ($target_admin_id === $_SESSION['admin_id']) {
    json_response(['success' => false, 'message' => 'You cannot perform this action on your own account'], 409);
}

try {
    // Cannot target super_admin
    $stmt = $pdo->prepare("SELECT admin_role, full_name, status FROM admin_users WHERE id = ?");
    $stmt->execute([$target_admin_id]);
    $target = $stmt->fetch();

    if (!$target) {
        json_response(['success' => false, 'message' => 'Admin not found'], 404);
    }
    if ($target['admin_role'] === 'super_admin') {
        json_response(['success' => false, 'message' => 'Super Admins cannot be modified'], 403);
    }

    switch ($action) {
        case 'suspend':
            if ($target['status'] === 'suspended') {
                json_response(['success' => false, 'message' => 'Admin is already suspended'], 409);
            }
            $stmt = $pdo->prepare("UPDATE admin_users SET status = 'suspended' WHERE id = ?");
            $stmt->execute([$target_admin_id]);
            write_audit_log($pdo, 'admin_suspended', $target_admin_id, 'admin', $_SESSION['admin_id'], 'admin', "Admin {$target['full_name']} suspended");
            $message = 'Admin suspended successfully';
            break;

        case 'activate':
            if ($target['status'] === 'active') {
                json_response(['success' => false, 'message' => 'Admin is already active'], 409);
            }
            $stmt = $pdo->prepare("UPDATE admin_users SET status = 'active' WHERE id = ?");
            $stmt->execute([$target_admin_id]);
            write_audit_log($pdo, 'admin_activated', $target_admin_id, 'admin', $_SESSION['admin_id'], 'admin', "Admin {$target['full_name']} activated");
            $message = 'Admin activated successfully';
            break;

        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
            $stmt->execute([$target_admin_id]);
            write_audit_log($pdo, 'admin_deleted', $target_admin_id, 'admin', $_SESSION['admin_id'], 'admin', "Admin {$target['full_name']} deleted");
            $message = 'Admin deleted successfully';
            break;

        default:
            json_response(['success' => false, 'message' => 'Invalid action'], 422);
    }

    json_response(['success' => true, 'message' => $message]);

} catch (PDOException $e) {
    error_log("Admin Action Error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Action failed. Please try again.'], 500);
}
