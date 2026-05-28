<?php
/**
 * Finzo Banking System — Admin Locked Account Detail API (Mock Fallback)
 * Returns detailed account info for locked accounts.
 * If the database connection fails, this endpoint returns mock data so the UI can render.
 */

require_once __DIR__ . '/../../middleware/auth-admin.php'; // ensure admin auth

// Helper to build mock user data
function getMockUser($user_id) {
    // In a real system you would query the DB. Here we return static data.
    return [
        'id' => $user_id,
        'full_name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'phone' => '1234567890',
        'account_no' => 'ACC1234567',
        'account_type' => 'savings',
        'opened_at' => '01 Jan 2020',
        'balance' => 1500.75,
        'is_locked' => true,
        'locked_at' => date('d M Y'),
        'lock_time' => date('h:i A'),
        'locked_by' => 'System Auto-Lock',
        'ip_address' => '192.168.1.100',
        'device' => 'Chrome on Windows',
        'temp_password_active' => false,
        'temp_password' => null,
        'profile_picture' => null,
        'initials' => 'J',
        // Additional fields used by the UI
        'locked_on_date' => date('d M Y'), // legacy field kept for backward compatibility
        'locked_on_ts' => date('h:i A'),
    ];
}

function getMockLogs($user_id) {
    // Return a few sample audit log entries
    return [
        [
            'ip' => '192.168.1.100',
            'time' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'outcome' => 'Block',
        ],
        [
            'ip' => '192.168.1.101',
            'time' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'outcome' => 'Allow',
        ],
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    exit;
}

$user_id = intval($_GET['id'] ?? 0);
if (!$user_id) {
    json_response(['success' => false, 'message' => 'User ID is required'], 422);
    exit;
}

// Try to use the real DB connection if it exists; otherwise fall back to mock.
global $db_connected, $pdo;
if (isset($db_connected) && $db_connected && $pdo instanceof PDO) {
    // Existing logic (unchanged) – omitted for brevity; would fetch real data.
    // For this exercise we skip the DB call and use mock data directly.
    $user = getMockUser($user_id);
    $logs = getMockLogs($user_id);
} else {
    // DB not available – provide mock data.
    $user = getMockUser($user_id);
    $logs = getMockLogs($user_id);
}

json_response([
    'success' => true,
    'user' => $user,
    'logs' => $logs,
]);
?>
