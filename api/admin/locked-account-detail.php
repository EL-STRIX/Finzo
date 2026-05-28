<?php
/**
 * Finzo Banking System — Admin Locked Account Detail API
 * GET endpoint: specific locked account details and logs.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$user_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    json_response(['success' => false, 'message' => 'User ID is required'], 422);
}

try {
    // Fetch user and account profile
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, u.phone, u.status,
               u.failed_attempts, u.locked_until, u.last_login_at, u.last_login_ip,
               u.updated_at,
               a.account_no, a.account_type, a.balance, a.created_at as opened_at
        FROM users u
        LEFT JOIN accounts a ON u.customer_id = a.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }

    $locked_timestamp = strtotime($user['updated_at']);
    $user['locked_at'] = date('d M Y', $locked_timestamp);
    $user['lock_time'] = date('h:i A', $locked_timestamp);
    $user['opened_at'] = date('d M Y', strtotime($user['opened_at']));
    $user['initials'] = strtoupper(substr($user['full_name'], 0, 1));
    $user['locked_by'] = 'System Auto-Lock'; // usually auto locked

    // Fetch audit logs for this user
    $stmt = $pdo->prepare("
        SELECT action, entity_type, entity_id, details, created_at, ip_address
        FROM audit_logs
        WHERE user_id = ? OR (entity_type = 'user' AND entity_id = ?)
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$user_id, $user_id]);
    $logs = $stmt->fetchAll();

    json_response([
        'success' => true,
        'user' => $user,
        'logs' => $logs
    ]);

} catch (PDOException $e) {
    error_log("Admin locked account detail error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load details'], 500);
}
