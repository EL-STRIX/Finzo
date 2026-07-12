<?php
/**
 * Finzo Banking System — Account Lookup API
 * GET endpoint: looks up a destination account by account number.
 * Used by the transfer page for recipient validation.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-user.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$account_no = clean_input($_GET['account_no'] ?? '');

if (empty($account_no)) {
    json_response(['found' => false, 'message' => 'Account number is required'], 422);
}

try {
    $stmt = $pdo->prepare("
        SELECT u.full_name, a.account_type, a.status
        FROM accounts a
        JOIN users u ON a.user_id = u.customer_id
        WHERE a.account_no = ?
    ");
    $stmt->execute([$account_no]);
    $result = $stmt->fetch();

    if (!$result || $result['status'] !== 'active') {
        json_response(['success' => false, 'message' => 'Finzo account not found']);
    }

    // Do not return balance or sensitive data
    json_response([
        'success' => true,
        'account' => [
            'holder_name'  => $result['full_name'],
            'account_type' => $result['account_type']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Account lookup error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Lookup failed'], 500);
}
