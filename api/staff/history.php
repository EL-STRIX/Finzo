<?php
/**
 * Finzo Banking System — Staff Transaction History API
 * GET endpoint: paginated history of all transactions processed by this staff member.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-staff.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$staff_id = $_SESSION['staff_id'];

try {
    // Parse filters
    $type      = clean_input($_GET['type'] ?? 'all');
    $status    = clean_input($_GET['status'] ?? 'all');
    $search    = clean_input($_GET['search'] ?? '');
    $from_date = clean_input($_GET['from_date'] ?? '');
    $to_date   = clean_input($_GET['to_date'] ?? '');
    $page      = max(1, intval($_GET['page'] ?? 1));
    $per_page  = min(50, max(10, intval($_GET['per_page'] ?? 20)));

    // Build query conditions
    $where_clauses = ["t.processed_by_staff_id = ?"];
    $params = [$staff_id];

    // Type filter
    if ($type !== 'all' && in_array($type, ['deposit', 'withdrawal'])) {
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
        $where_clauses[] = "(t.txn_id LIKE ? OR t.description LIKE ? OR a.account_no LIKE ? OR u.full_name LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Count total
    $count_sql = "
        SELECT COUNT(*)
        FROM transactions t
        LEFT JOIN accounts a ON COALESCE(t.to_account_id, t.from_account_id) = a.id
        LEFT JOIN users u ON a.user_id = u.customer_id
        WHERE {$where_sql}
    ";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pages  = max(1, ceil($total / $per_page));
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
            COALESCE(a.account_no, 'N/A') as account_no,
            COALESCE(u.full_name, 'N/A') as customer_name
        FROM transactions t
        LEFT JOIN accounts a ON COALESCE(t.to_account_id, t.from_account_id) = a.id
        LEFT JOIN users u ON a.user_id = u.customer_id
        WHERE {$where_sql}
        ORDER BY t.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

    json_response([
        'success'      => true,
        'transactions' => $transactions,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page,
        'per_page'     => $per_page
    ]);

} catch (PDOException $e) {
    error_log("Staff history error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load transaction history'], 500);
}
