<?php
/**
 * Finzo Banking System — Account Details API
 * GET endpoint: returns account details for the authenticated user.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-user.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch account details
    $stmt = $pdo->prepare("
        SELECT
            a.id, a.account_no, a.account_type, a.balance, a.interest_rate,
            a.daily_limit, a.status, a.opened_date, a.created_at,
            u.full_name, u.email, u.phone
        FROM accounts a
        JOIN users u ON a.user_id = u.customer_id
        WHERE u.id = ?
        ORDER BY a.status = 'active' DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $account = $stmt->fetch();

    if (!$account) {
        json_response([
            'success' => true,
            'account' => null,
            'message' => 'No account found'
        ]);
    }

    // Get mini statement (last 10 transactions)
    $mini_statement = [];
    if ($account['status'] === 'active') {
        $stmt = $pdo->prepare("
            SELECT txn_id, type, amount, balance_after, description, status, created_at
            FROM transactions
            WHERE (from_account_id = ? OR to_account_id = ?) AND status = 'completed'
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$account['id'], $account['id']]);
        $mini_statement = $stmt->fetchAll();
    }

    // Calculate today's transfer total
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as today_total
        FROM transactions
        WHERE from_account_id = ? AND type = 'transfer'
            AND status = 'completed'
            AND DATE(created_at) = CURDATE()
    ");
    $stmt->execute([$account['id']]);
    $today_transfer_total = (float)$stmt->fetchColumn();

    json_response([
        'success' => true,
        'account' => [
            'id'             => (int)$account['id'],
            'account_no'     => $account['account_no'],
            'account_type'   => $account['account_type'],
            'balance'        => (float)$account['balance'],
            'interest_rate'  => (float)$account['interest_rate'],
            'daily_limit'    => (float)$account['daily_limit'],
            'status'         => $account['status'],
            'opened_date'    => $account['opened_date'],
            'holder_name'    => $account['full_name'],
            'email'          => $account['email'],
            'phone'          => $account['phone']
        ],
        'mini_statement'       => $mini_statement,
        'today_transfer_total' => $today_transfer_total,
        'remaining_limit'      => max(0, (float)$account['daily_limit'] - $today_transfer_total)
    ]);

} catch (PDOException $e) {
    error_log("Accounts API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load account details'], 500);
}
