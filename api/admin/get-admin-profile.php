<?php
/**
 * Finzo Banking System — Get Admin Profile API
 * GET endpoint: returns the logged-in admin's profile and recent activity.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$admin_id = $_SESSION['admin_id'];

try {
    $stmt = $pdo->prepare("
        SELECT id, full_name, email, phone, date_of_birth, gender, profile_photo,
               admin_role, access_level, status, two_fa_enabled, 
               last_login_at, last_login_ip, last_password_changed, failed_attempts
        FROM admin_users 
        WHERE id = ?
    ");
    $stmt->execute([$admin_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        json_response(['success' => false, 'message' => 'Profile not found'], 404);
    }

    $stmt = $pdo->prepare("
        SELECT action_type, details, ip_address, created_at
        FROM audit_log
        WHERE actor_id = ? AND actor_role = 'admin'
        ORDER BY created_at DESC LIMIT 100
    ");
    $stmt->execute([$admin_id]);
    $activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    json_response([
        'success' => true,
        'profile' => $profile,
        'activity' => $activity
    ]);

} catch (PDOException $e) {
    error_log("Get Admin Profile Error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load profile'], 500);
}
