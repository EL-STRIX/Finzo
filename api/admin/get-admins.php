<?php
/**
 * Finzo Banking System — Get Admins API
 * GET endpoint: returns all admins for super_admin management view.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

if ($_SESSION['admin_role'] !== 'super_admin') {
    json_response(['success' => false, 'message' => 'Forbidden'], 403);
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            a.id, a.admin_id, a.full_name, a.email, a.phone, a.gender, a.profile_photo, 
            a.admin_role, a.access_level, a.status, a.last_login_at, a.created_at,
            (SELECT full_name FROM admin_users WHERE id = a.created_by) AS created_by_name
        FROM admin_users a
        WHERE a.admin_role != 'super_admin'
        ORDER BY a.created_at DESC
    ");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    json_response([
        'success' => true,
        'admins' => $admins,
        'total' => count($admins)
    ]);

} catch (PDOException $e) {
    error_log("Get Admins Error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to retrieve admins.'], 500);
}
