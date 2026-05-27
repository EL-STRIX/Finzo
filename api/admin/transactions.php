<?php
/**
 * Finzo Banking System — Admin Transactions Overview API
 * GET endpoint: all transactions with filters, search, and CSV export.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $search    = sanitize_input($_GET['search'] ?? '');
    $type      = sanitize_input($_GET['type'] ?? 'all');
    $status    = sanitize_input($_GET['status'] ?? 'all');
    $from_date = sanitize_input($_GET['from_date'] ?? '');
    $to_date   = sanitize_input($_GET['to_date'] ?? '');
    $page      = max(1, intval($_GET['page'] ?? 1));
    $per_page  = min(50, max(10, intval($_GET['per_page'] ?? 20)));
    $export    = sanitize_input($_GET['export'] ?? '');

    // Build query
    $where_clauses = ["1=1"];
    $params = [];

    if ($type !== 'all' && in_array($type, ['deposit', 'withdrawal', 'transfer', 'interest_credit'])) {
        $where_clauses[] = "t.type = ?";
        $params[] = $type;
    }

    if ($status !== 'all' && in_array($status, ['completed', 'pending', 'failed', 'reversed'])) {
        $where_clauses[] = "t.status = ?";
        $params[] = $status;
    }

    if (!empty($from_date)) {
        $where_clauses[] = "DATE(t.created_at) >= ?";
        $params[] = $from_date;
    }

    if (!empty($to_date)) {
        $where_clauses[] = "DATE(t.created_at) <= ?";
        $params[] = $to_date;
    }

    if (!empty($search)) {
        $where_clauses[] = "(t.txn_id LIKE ? OR fu.full_name LIKE ? OR tu.full_name LIKE ? OR fa.account_no LIKE ? OR ta.account_no LIKE ?)";
        $s = "%{$search}%";
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
    }

    $where_sql = implode(' AND ', $where_clauses);

    $base_sql = "
        FROM transactions t
        LEFT JOIN accounts fa ON t.from_account_id = fa.id
        LEFT JOIN users fu ON fa.user_id = fu.customer_id
        LEFT JOIN accounts ta ON t.to_account_id = ta.id
        LEFT JOIN users tu ON ta.user_id = tu.customer_id
        LEFT JOIN staff s ON t.processed_by_staff_id = s.id
        WHERE {$where_sql}
    ";

    // =====================================================
    // CSV EXPORT
    // =====================================================
    if ($export === 'csv') {
        $sql = "
            SELECT
                t.txn_id as 'Transaction ID',
                COALESCE(fa.account_no, 'N/A') as 'From Account',
                COALESCE(fu.full_name, 'N/A') as 'From Name',
                COALESCE(ta.account_no, 'N/A') as 'To Account',
                COALESCE(tu.full_name, 'N/A') as 'To Name',
                t.type as 'Type',
                t.amount as 'Amount',
                t.status as 'Status',
                t.description as 'Description',
                COALESCE(s.staff_id, 'N/A') as 'Processed By',
                t.created_at as 'Date'
            {$base_sql}
            ORDER BY t.created_at DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Output CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="finzo_transactions_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        if (!empty($rows)) {
            fputcsv($output, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
        }
        fclose($output);
        exit;
    }

    // =====================================================
    // JSON RESPONSE (paginated)
    // =====================================================
    $count_sql = "SELECT COUNT(*) {$base_sql}";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pages  = max(1, ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;

    $sql = "
        SELECT
            t.id, t.txn_id, t.type, t.amount, t.balance_after, t.description,
            t.reference_note, t.status, t.created_at,
            COALESCE(fa.account_no, 'N/A') as from_account_no,
            COALESCE(fu.full_name, 'N/A') as from_user_name,
            fu.profile_picture as from_user_avatar,
            COALESCE(ta.account_no, 'N/A') as to_account_no,
            COALESCE(tu.full_name, 'N/A') as to_user_name,
            tu.profile_picture as to_user_avatar,
            COALESCE(s.staff_id, '') as processed_by
        {$base_sql}
        ORDER BY t.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

    // Summary stats
    $summary_sql = "
        SELECT
            COUNT(*) as total_count,
            COALESCE(SUM(t.amount), 0) as total_amount,
            COALESCE(SUM(CASE WHEN t.type = 'deposit' THEN t.amount ELSE 0 END), 0) as total_deposits,
            COALESCE(SUM(CASE WHEN t.type = 'withdrawal' THEN t.amount ELSE 0 END), 0) as total_withdrawals,
            COALESCE(SUM(CASE WHEN t.type = 'transfer' THEN t.amount ELSE 0 END), 0) as total_transfers
        {$base_sql}
    ";
    $stmt = $pdo->prepare($summary_sql);
    $stmt->execute($params);
    $summary = $stmt->fetch();

    json_response([
        'success'      => true,
        'transactions' => $transactions,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page,
        'per_page'     => $per_page,
        'summary'      => $summary
    ]);

} catch (PDOException $e) {
    error_log("Admin transactions API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load transactions'], 500);
}
