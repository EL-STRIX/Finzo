<?php
/**
 * Finzo Banking System — Customer Dashboard API
 * GET endpoint: returns all data needed for the customer dashboard.
 * Replaces all localStorage-based UserState from user-shared.js.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-user.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$user_id = $_SESSION['user_id'];

try {
    // =====================================================
    // FETCH USER DATA
    // =====================================================
    $stmt = $pdo->prepare("
        SELECT id, customer_id, full_name, email, phone, status, rejection_reason, created_at
        FROM users
        WHERE id = ? AND is_deleted = 0
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        destroy_session();
        json_response(['success' => false, 'message' => 'User not found', 'redirect' => '/views/auth/login.html'], 404);
    }

    // Update session status in case it changed
    $_SESSION['user_status'] = $user['status'];

    // =====================================================
    // FETCH ACCOUNT DATA
    // =====================================================
    $account = null;
    $stmt = $pdo->prepare("
        SELECT id, account_no, account_type, balance, interest_rate, daily_limit, status, opened_date
        FROM accounts
        WHERE user_id = ?
        ORDER BY status = 'active' DESC, created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$user['customer_id']]);
    $account = $stmt->fetch();

    // =====================================================
    // RECENT TRANSACTIONS (last 5)
    // =====================================================
    $recent_transactions = [];
    if ($account && $account['status'] === 'active') {
        $stmt = $pdo->prepare("
            SELECT
                t.txn_id,
                t.type,
                t.amount,
                t.description,
                t.status,
                t.created_at,
                CASE
                    WHEN t.type = 'transfer' AND t.from_account_id = ? THEN
                        (SELECT u.full_name FROM accounts a JOIN users u ON a.user_id = u.customer_id WHERE a.id = t.to_account_id)
                    WHEN t.type = 'transfer' AND t.to_account_id = ? THEN
                        (SELECT u.full_name FROM accounts a JOIN users u ON a.user_id = u.customer_id WHERE a.id = t.from_account_id)
                    ELSE NULL
                END as counterparty
            FROM transactions t
            WHERE (t.from_account_id = ? OR t.to_account_id = ?)
                AND t.status = 'completed'
            ORDER BY t.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$account['id'], $account['id'], $account['id'], $account['id']]);
        $recent_transactions = $stmt->fetchAll();

        // Add direction info (credit/debit) to each transaction
        foreach ($recent_transactions as &$txn) {
            if ($txn['type'] === 'transfer') {
                // Determine if this is a credit or debit for the current user
                $txn['direction'] = ($txn['counterparty'] === null || $account['id'] === null)
                    ? 'debit' : 'debit'; // default
                // Check if money was received
                $check = $pdo->prepare("SELECT to_account_id FROM transactions WHERE txn_id = ?");
                $check->execute([$txn['txn_id']]);
                $txn_data = $check->fetch();
                $txn['direction'] = ($txn_data && $txn_data['to_account_id'] == $account['id']) ? 'credit' : 'debit';
            } elseif ($txn['type'] === 'deposit' || $txn['type'] === 'interest_credit') {
                $txn['direction'] = 'credit';
            } else {
                $txn['direction'] = 'debit';
            }
        }
        unset($txn);
    }

    // =====================================================
    // MONTHLY STATS (current month income vs expenses)
    // =====================================================
    $monthly_stats = [
        'income_total'  => 0,
        'expense_total' => 0,
        'month_label'   => date('F Y')
    ];

    if ($account && $account['status'] === 'active') {
        // Income: deposits + incoming transfers + interest
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM transactions
            WHERE to_account_id = ?
                AND status = 'completed'
                AND MONTH(created_at) = MONTH(CURDATE())
                AND YEAR(created_at) = YEAR(CURDATE())
        ");
        $stmt->execute([$account['id']]);
        $monthly_stats['income_total'] = (float)$stmt->fetchColumn();

        // Expenses: withdrawals + outgoing transfers
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM transactions
            WHERE from_account_id = ?
                AND status = 'completed'
                AND MONTH(created_at) = MONTH(CURDATE())
                AND YEAR(created_at) = YEAR(CURDATE())
        ");
        $stmt->execute([$account['id']]);
        $monthly_stats['expense_total'] = (float)$stmt->fetchColumn();
    }

    // =====================================================
    // NOTIFICATION COUNT (unread)
    // =====================================================
    // Fetch unread notifications count using customer_id
    if (!isset($_SESSION['user_customer_id'])) {
        $stmt_cid = $pdo->prepare("SELECT customer_id FROM users WHERE id = ?");
        $stmt_cid->execute([$user_id]);
        $_SESSION['user_customer_id'] = $stmt_cid->fetchColumn();
    }
    $customer_id = $_SESSION['user_customer_id'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$customer_id]);
    $notification_count = (int)$stmt->fetchColumn();

    // =====================================================
    // GREETING
    // =====================================================
    $greeting = get_greeting();

    // Avatar URL
    $avatar_path = dirname(__DIR__, 2) . '/uploads/avatars/user_' . $user_id . '.jpg';
    $avatar_url = file_exists($avatar_path) ? '../../uploads/avatars/user_' . $user_id . '.jpg?t=' . filemtime($avatar_path) : null;

    // =====================================================
    // RESPONSE
    // =====================================================
    json_response([
        'success' => true,
        'user' => [
            'id'               => (int)$user['id'],
            'customer_id'      => $user['customer_id'],
            'name'             => $user['full_name'],
            'email'            => $user['email'],
            'phone'            => $user['phone'],
            'status'           => $user['status'],
            'rejection_reason' => $user['rejection_reason'],
            'member_since'     => $user['created_at'],
            'avatar_url'       => $avatar_url
        ],
        'account' => $account ? [
            'account_no'    => $account['account_no'],
            'account_type'  => $account['account_type'],
            'balance'       => (float)$account['balance'],
            'interest_rate' => (float)$account['interest_rate'],
            'daily_limit'   => (float)$account['daily_limit'],
            'status'        => $account['status'],
            'opened_date'   => $account['opened_date']
        ] : null,
        'recent_transactions' => $recent_transactions,
        'monthly_stats'       => $monthly_stats,
        'notification_count'  => $notification_count,
        'greeting'            => $greeting
    ]);

} catch (PDOException $e) {
    error_log("Dashboard API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load dashboard data'], 500);
}
