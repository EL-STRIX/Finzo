<?php
/**
 * Finzo Banking System — Admin Users List API
 * GET endpoint: paginated customer list with search and filters.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $search       = sanitize_input($_GET['search'] ?? '');
    $status       = sanitize_input($_GET['status'] ?? 'all');
    $account_type = sanitize_input($_GET['account_type'] ?? 'all');
    $page         = max(1, intval($_GET['page'] ?? 1));
    $per_page     = min(50, max(10, intval($_GET['per_page'] ?? 20)));

    // Build query
    $where_clauses = ["u.is_deleted = 0"];
    $params = [];

    // Status filter
    if ($status !== 'all' && in_array($status, ['pending', 'under_review', 'active', 'suspended', 'rejected'])) {
        $where_clauses[] = "u.status = ?";
        $params[] = $status;
    }

    // Account type filter
    if ($account_type !== 'all' && in_array($account_type, ['savings', 'current'])) {
        $where_clauses[] = "a.account_type = ?";
        $params[] = $account_type;
    }

    // Search
    if (!empty($search)) {
        $where_clauses[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR a.account_no LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Count total
    $count_sql = "
        SELECT COUNT(DISTINCT u.id)
        FROM users u
        LEFT JOIN accounts a ON u.customer_id = a.user_id
        WHERE {$where_sql}
    ";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pages  = max(1, ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;

    // Fetch users
    $sql = "
        SELECT
            u.id, u.customer_id, u.full_name, u.email, u.phone, u.status, u.created_at,
            a.account_no, a.account_type, a.balance, a.status as account_status
        FROM users u
        LEFT JOIN accounts a ON u.customer_id = a.user_id
        WHERE {$where_sql}
        ORDER BY u.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    json_response([
        'success'      => true,
        'users'        => $users,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page,
        'per_page'     => $per_page
    ]);

} catch (PDOException $e) {
    error_log("Admin users API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load users'], 500);
}
