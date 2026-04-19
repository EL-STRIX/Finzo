<?php
/**
 * Finzo Banking System — Admin Deleted Users API
 * GET endpoint: list of all soft-deleted user accounts.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $search   = sanitize_input($_GET['search'] ?? '');
    $page     = max(1, intval($_GET['page'] ?? 1));
    $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

    $where_clauses = ["u.is_deleted = 1"];
    $params = [];

    if (!empty($search)) {
        $where_clauses[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
        $s = "%{$search}%";
        $params = [$s, $s, $s];
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users u WHERE {$where_sql}");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pages  = max(1, ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;

    // Fetch
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, u.phone, u.status, u.deleted_at, u.created_at,
               a.account_no, a.account_type, a.balance
        FROM users u
        LEFT JOIN accounts a ON u.id = a.user_id
        WHERE {$where_sql}
        ORDER BY u.deleted_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    json_response([
        'success'      => true,
        'users'        => $users,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page
    ]);

} catch (PDOException $e) {
    error_log("Deleted users API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load deleted users'], 500);
}
