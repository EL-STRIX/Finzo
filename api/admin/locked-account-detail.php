<?php
/**
 * Finzo Banking System — Admin Locked Account Detail API
 * GET endpoint: full locked-account profile with audit logs.
 * Version 1.1 — May 2026
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
    // ── 1. Fetch user + account info ──
    $stmt = $pdo->prepare("
        SELECT u.id, u.customer_id, u.full_name, u.email, u.phone, u.status,
               u.profile_picture, u.failed_attempts, u.locked_until,
               u.last_login_at, u.last_login_ip, u.updated_at,
               a.account_no, a.account_type, a.balance, a.opened_date
        FROM users u
        LEFT JOIN accounts a ON u.customer_id = a.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }

    // ── 2. Compute derived fields the JS expects ──
    // Account is considered locked if locked_until is set (even if expired) AND failed_attempts >= 5
    // The lock is only truly cleared when an admin resets failed_attempts to 0
    $is_locked = ($user['failed_attempts'] >= 5) || ($user['locked_until'] && strtotime($user['locked_until']) > time());

    // Lock timestamp — use locked_until or updated_at as best approximation
    $lock_ts = $user['locked_until'] ? strtotime($user['locked_until']) : strtotime($user['updated_at']);

    $user['is_locked']        = $is_locked;
    $user['initials']         = strtoupper(substr($user['full_name'], 0, 1));
    $user['locked_at']        = date('d M Y', $lock_ts);
    $user['lock_time']        = date('h:i A', $lock_ts);
    $user['locked_on_date']   = date('d M Y', $lock_ts);
    $user['locked_on_ts']     = date('h:i A', $lock_ts);
    $user['locked_by']        = 'System Auto-Lock';
    $user['ip_address']       = $user['last_login_ip'] ?? 'N/A';
    $user['device']           = 'N/A'; // no user-agent stored
    $user['opened_at']        = $user['opened_date'] ? date('d M Y', strtotime($user['opened_date'])) : 'N/A';
    $user['temp_password_active'] = false;
    $user['temp_password']    = null;

    // Check if there is an active temporary password generated in the last 5 minutes
    if (isset($_SESSION['temp_passwords'][$user_id])) {
        $temp_data = $_SESSION['temp_passwords'][$user_id];
        if ($temp_data['expires'] > time()) {
            $user['temp_password_active'] = true;
            $user['temp_password'] = $temp_data['password'];
            // Force the account to still appear "locked" in the UI context so the red badges stay
            // even though the DB says it's technically unlocked (so they can still see it's a locked context)
            $is_locked = true; 
            $user['is_locked'] = true;
        } else {
            // Clean up expired
            unset($_SESSION['temp_passwords'][$user_id]);
        }
    }

    // ── 3. Fetch audit logs related to this user (by customer_id) ──
    $customer_id = $user['customer_id'];
    $logs = [];

    if ($customer_id) {
        $stmt = $pdo->prepare("
            SELECT action_type, entity_type, entity_id, ip_address, created_at, details
            FROM audit_log
            WHERE (entity_type = 'user' AND entity_id = ?)
               OR (actor_id = ? AND actor_role = 'User')
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$customer_id, $customer_id]);
        $raw_logs = $stmt->fetchAll();

        // Map to the shape the JS renderAuditLogs() expects
        foreach ($raw_logs as $log) {
            $outcome = 'Allow';
            $action = strtolower($log['action_type'] ?? '');
            if (strpos($action, 'lock') !== false || strpos($action, 'fail') !== false || strpos($action, 'block') !== false) {
                $outcome = 'Block';
            }

            $logs[] = [
                'ip'      => $log['ip_address'] ?? '::1',
                'time'    => date('d M Y, h:i A', strtotime($log['created_at'])),
                'outcome' => $outcome,
                'action'  => $log['action_type'],
                'details' => $log['details'],
            ];
        }
    }

    // ── 4. Return ──
    json_response([
        'success' => true,
        'user'    => $user,
        'logs'    => $logs
    ]);

} catch (PDOException $e) {
    error_log("Admin locked account detail error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load details: ' . $e->getMessage()], 500);
}
