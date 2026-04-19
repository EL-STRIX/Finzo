<?php
/**
 * Finzo Banking System — Staff Account Lookup API
 * GET endpoint: looks up a destination account by account number for staff.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-staff.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$account_no = sanitize_input($_GET['account_no'] ?? '');

if (empty($account_no)) {
    json_response(['success' => false, 'found' => false, 'message' => 'Account number is required'], 422);
}

try {
    $stmt = $pdo->prepare("
        SELECT u.full_name, a.account_no, a.account_type, a.status, a.balance, a.daily_limit
        FROM accounts a
        JOIN users u ON a.user_id = u.id
        WHERE a.account_no = ?
    ");
    $stmt->execute([$account_no]);
    $result = $stmt->fetch();

    if (!$result) {
        json_response(['success' => true, 'found' => false]);
    }

    // Calculate daily limit used
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) FROM transactions 
        WHERE from_account_id = (SELECT id FROM accounts WHERE account_no = ?)
        AND DATE(created_at) = CURDATE() AND status != 'failed'
    ");
    $stmt->execute([$account_no]);
    $used_today = $stmt->fetchColumn();
    $limit_left = max(0, $result['daily_limit'] - $used_today);

    json_response([
        'success'      => true,
        'found'        => true,
        'account'      => [
            'name'         => $result['full_name'],
            'account_no'   => $result['account_no'],
            'account_type' => $result['account_type'],
            'status'       => $result['status'],
            'balance'      => $result['balance'],
            'daily_limit_left' => $limit_left
        ]
    ]);

} catch (PDOException $e) {
    error_log("Account lookup error: " . $e->getMessage());
    json_response(['success' => false, 'found' => false, 'message' => 'Lookup failed'], 500);
}
