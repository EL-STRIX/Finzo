<?php
/**
 * Finzo Banking System — Admin Management Actions API
 * POST endpoint: Suspend, Activate, Delete, or Reset Password for an admin.
 * Requires Super Admin privileges.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// 1. CSRF Validation
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid or expired session token'], 403);
}

// 2. Privilege Check
if ($_SESSION['admin_role'] !== 'super_admin') {
    json_response(['success' => false, 'message' => 'Forbidden: Only Super Administrator can perform this action.'], 403);
}

$action = sanitize_input($_POST['action'] ?? '');
$target_id = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : 0;

if ($target_id <= 0) {
    json_response(['success' => false, 'message' => 'Invalid Admin ID'], 400);
}

// Do not allow super admin to modify themselves via this route
if ($target_id === (int)$_SESSION['admin_id']) {
    json_response(['success' => false, 'message' => 'Self-management not permitted via this interface.'], 400);
}

try {
    // Check if target admin exists and is not a super admin
    $check_stmt = $pdo->prepare("SELECT full_name, admin_role, status FROM admin_users WHERE id = ?");
    $check_stmt->execute([$target_id]);
    $target = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$target) {
        json_response(['success' => false, 'message' => 'Admin not found.'], 404);
    }
    
    if ($target['admin_role'] === 'super_admin') {
        json_response(['success' => false, 'message' => 'Cannot modify a Super Administrator.'], 403);
    }

    $response = ['success' => false, 'message' => 'Invalid action'];

    switch ($action) {
        case 'toggle_status':
            $new_status = ($target['status'] === 'active') ? 'suspended' : 'active';
            $stmt = $pdo->prepare("UPDATE admin_users SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $target_id]);
            
            write_audit_log($pdo, 'admin_status_change', $target_id, 'admin', $_SESSION['admin_id'], 'admin', "Changed status of {$target['full_name']} to {$new_status}");
            
            $response = [
                'success' => true, 
                'message' => "Admin account has been " . ($new_status === 'active' ? 'activated' : 'suspended') . " successfully.",
                'new_status' => $new_status
            ];
            break;

        case 'delete':
            // Permanent deletion or soft delete? 
            // For admins, we do hard delete but log it first.
            $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
            $stmt->execute([$target_id]);
            
            write_audit_log($pdo, 'admin_deleted', $target_id, 'admin', $_SESSION['admin_id'], 'admin', "Permanently deleted admin account: {$target['full_name']}");
            
            $response = ['success' => true, 'message' => "Admin account has been permanently removed from the system."];
            break;

        case 'reset_password':
            $new_password = $_POST['new_password'] ?? '';
            if (strlen($new_password) < 8) {
                json_response(['success' => false, 'message' => 'Password must be at least 8 characters long.'], 422);
            }
            
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ?, last_password_changed = NOW(), failed_attempts = 0 WHERE id = ?");
            $stmt->execute([$hash, $target_id]);
            
            write_audit_log($pdo, 'admin_password_reset', $target_id, 'admin', $_SESSION['admin_id'], 'admin', "Forced password reset for {$target['full_name']}");
            
            $response = ['success' => true, 'message' => "Password has been successfully updated for {$target['full_name']}."];
            break;
    }

    json_response($response);

} catch (PDOException $e) {
    error_log("Admin Action Error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Database error during administrative action.'], 500);
}
