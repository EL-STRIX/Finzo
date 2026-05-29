<?php
/**
 * Finzo Banking System — Transaction History API
 * GET endpoint: paginated transaction list with search and filters.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-user.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$user_id = $_SESSION['user_id'];

try {
    // Get user's account ID
    $stmt = $pdo->prepare("SELECT a.id FROM accounts a JOIN users u ON a.user_id = u.customer_id WHERE u.id = ? AND a.status = 'active' LIMIT 1");
    $stmt->execute([$user_id]);
    $account = $stmt->fetch();

    if (!$account) {
        json_response([
            'success'      => true,
            'transactions' => [],
            'total'        => 0,
            'pages'        => 0,
            'current_page' => 1,
            'message'      => 'No active account found'
        ]);
    }

    $account_id = $account['id'];

    // Parse filters
    $search    = clean_input($_GET['search'] ?? '');
    $type      = clean_input($_GET['type'] ?? 'all');
    $status    = clean_input($_GET['status'] ?? 'all');
    $from_date = clean_input($_GET['from_date'] ?? '');
    $to_date   = clean_input($_GET['to_date'] ?? '');
    $page      = max(1, intval($_GET['page'] ?? 1));
    $per_page  = min(50, max(10, intval($_GET['per_page'] ?? 20)));

    // Build query
    $where_clauses = ["(t.from_account_id = ? OR t.to_account_id = ?)"];
    $params = [$account_id, $account_id];

    // Type filter
    if ($type !== 'all' && in_array($type, ['deposit', 'withdrawal', 'transfer', 'interest_credit'])) {
        $where_clauses[] = "t.type = ?";
        $params[] = $type;
    }

    // Status filter
    if ($status !== 'all' && in_array($status, ['completed', 'pending', 'failed', 'reversed'])) {
        $where_clauses[] = "t.status = ?";
        $params[] = $status;
    }

    // Date range
    if (!empty($from_date)) {
        $where_clauses[] = "DATE(t.created_at) >= ?";
        $params[] = $from_date;
    }
    if (!empty($to_date)) {
        $where_clauses[] = "DATE(t.created_at) <= ?";
        $params[] = $to_date;
    }

    // Search
    if (!empty($search)) {
        $where_clauses[] = "(t.txn_id LIKE ? OR t.description LIKE ? OR t.reference_note LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Count total
    $count_sql = "SELECT COUNT(*) FROM transactions t WHERE {$where_sql}";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pages = max(1, ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;

    // Fetch transactions
    $sql = "
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
            fa.account_no as from_account_no,
            COALESCE(fu.full_name, 'N/A') as from_name,
            ta.account_no as to_account_no,
            COALESCE(tu.full_name, 'N/A') as to_name
        FROM transactions t
        LEFT JOIN accounts fa ON t.from_account_id = fa.id
        LEFT JOIN users fu ON fa.user_id = fu.customer_id
        LEFT JOIN accounts ta ON t.to_account_id = ta.id
        LEFT JOIN users tu ON ta.user_id = tu.customer_id
        WHERE {$where_sql}
        ORDER BY t.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

    // Add direction (credit/debit) for each transaction
    foreach ($transactions as &$txn) {
        if ($txn['type'] === 'deposit' || $txn['type'] === 'interest_credit') {
            $txn['direction'] = 'credit';
        } elseif ($txn['type'] === 'withdrawal') {
            $txn['direction'] = 'debit';
        } elseif ($txn['type'] === 'transfer') {
            $txn['direction'] = ($txn['to_account_id'] == $account_id) ? 'credit' : 'debit';
        }
    }
    unset($txn);

    json_response([
        'success'      => true,
        'transactions' => $transactions,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page,
        'per_page'     => $per_page
    ]);

} catch (PDOException $e) {
    error_log("Transactions API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load transactions'], 500);
}
