<?php
/**
 * Finzo Banking System — Account Statement API
 * GET endpoint: returns statement data for a given date range.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-user.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$user_id = $_SESSION['user_id'];

try {
    // Get user's active account
    $stmt = $pdo->prepare("
        SELECT a.id, a.account_no, a.account_type, a.balance, a.opened_date, u.full_name
        FROM accounts a
        JOIN users u ON a.user_id = u.customer_id
        WHERE u.id = ? AND a.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $account = $stmt->fetch();

    if (!$account) {
        json_response(['success' => false, 'message' => 'No active account found'], 404);
    }

    // Parse date range (default: last 30 days)
    $from_date = sanitize_input($_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days')));
    $to_date   = sanitize_input($_GET['to_date'] ?? date('Y-m-d'));

    // Fetch transactions within date range
    $stmt = $pdo->prepare("
        SELECT
            t.txn_id,
            t.type,
            t.amount,
            t.balance_after,
            t.description,
            t.reference_note,
            t.status,
            t.created_at,
            t.from_account_id,
            t.to_account_id,
            COALESCE(fa.account_no, '') as from_account_no,
            COALESCE(ta.account_no, '') as to_account_no,
            COALESCE(fu.full_name, '') as from_name,
            COALESCE(tu.full_name, '') as to_name
        FROM transactions t
        LEFT JOIN accounts fa ON t.from_account_id = fa.id
        LEFT JOIN users fu ON fa.user_id = fu.customer_id
        LEFT JOIN accounts ta ON t.to_account_id = ta.id
        LEFT JOIN users tu ON ta.user_id = tu.customer_id
        WHERE (t.from_account_id = ? OR t.to_account_id = ?)
            AND t.status = 'completed'
            AND DATE(t.created_at) BETWEEN ? AND ?
        ORDER BY t.created_at ASC
    ");
    $stmt->execute([$account['id'], $account['id'], $from_date, $to_date]);
    $transactions = $stmt->fetchAll();

    // Calculate opening balance (balance before from_date)
    $stmt = $pdo->prepare("
        SELECT balance_after
        FROM transactions
        WHERE (from_account_id = ? OR to_account_id = ?)
            AND status = 'completed'
            AND DATE(created_at) < ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$account['id'], $account['id'], $from_date]);
    $opening_row = $stmt->fetch();
    $opening_balance = $opening_row ? (float)$opening_row['balance_after'] : 0;

    // Process transactions with running balance and direction
    $running_balance = $opening_balance;
    foreach ($transactions as &$txn) {
        if ($txn['type'] === 'deposit' || $txn['type'] === 'interest_credit' ||
            ($txn['type'] === 'transfer' && $txn['to_account_id'] == $account['id'])) {
            $txn['direction'] = 'credit';
            $txn['debit']  = 0;
            $txn['credit'] = (float)$txn['amount'];
        } else {
            $txn['direction'] = 'debit';
            $txn['debit']  = (float)$txn['amount'];
            $txn['credit'] = 0;
        }
    }
    unset($txn);

    // Calculate totals
    $total_credits = array_sum(array_column($transactions, 'credit'));
    $total_debits  = array_sum(array_column($transactions, 'debit'));
    $closing_balance = (float)$account['balance'];

    json_response([
        'success' => true,
        'account' => [
            'account_no'   => $account['account_no'],
            'account_type' => $account['account_type'],
            'holder_name'  => $account['full_name']
        ],
        'statement' => [
            'from_date'       => $from_date,
            'to_date'         => $to_date,
            'opening_balance' => $opening_balance,
            'closing_balance' => $closing_balance,
            'total_credits'   => $total_credits,
            'total_debits'    => $total_debits,
            'transactions'    => $transactions
        ]
    ]);

} catch (PDOException $e) {
    error_log("Statement API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to generate statement'], 500);
}
