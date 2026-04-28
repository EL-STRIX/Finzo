<?php
/**
 * Finzo Banking System — Get Admin Detail API
 * GET endpoint: returns detailed info for a specific admin.
 * Requires Super Admin privileges.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Security: Only super_admin can view other admin details
if ($_SESSION['admin_role'] !== 'super_admin') {
    json_response(['success' => false, 'message' => 'Forbidden: Insufficient privileges.'], 403);
}

$target_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($target_id <= 0) {
    json_response(['success' => false, 'message' => 'Invalid Admin ID'], 400);
}

// Do not allow super admin to manage themselves via this specific route 
// (they should use the profile page)
if ($target_id === (int)$_SESSION['admin_id']) {
    json_response(['success' => false, 'message' => 'Please use your profile page for self-management.'], 400);
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            a.id, a.full_name, a.email, a.phone, a.date_of_birth, a.gender, a.profile_photo,
            a.admin_role, a.access_level, a.status, a.two_fa_enabled, 
            a.last_login_at, a.last_login_ip, a.last_password_changed, a.failed_attempts, a.created_at,
            (SELECT full_name FROM admin_users WHERE id = a.created_by) AS created_by_name
        FROM admin_users a
        WHERE a.id = ? AND a.admin_role != 'super_admin'
    ");
    $stmt->execute([$target_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        json_response(['success' => false, 'message' => 'Admin not found or restricted.'], 404);
    }

    // Fetch activity for this specific admin
    $stmt = $pdo->prepare("
        SELECT action_type, details, ip_address, created_at
        FROM audit_log
        WHERE actor_id = ? AND actor_role = 'admin'
        ORDER BY created_at DESC LIMIT 50
    ");
    $stmt->execute([$target_id]);
    $activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    json_response([
        'success' => true,
        'admin' => $admin,
        'activity' => $activity
    ]);

} catch (PDOException $e) {
    error_log("Get Admin Detail Error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load admin details.'], 500);
}
