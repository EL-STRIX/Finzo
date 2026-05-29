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
    $search       = clean_input($_GET['search'] ?? '');
    $date_from    = clean_input($_GET['date_from'] ?? '');
    $date_to      = clean_input($_GET['date_to'] ?? '');
    $account_type = clean_input($_GET['account_type'] ?? '');
    $page         = max(1, intval($_GET['page'] ?? 1));
    $per_page     = min(50, max(10, intval($_GET['per_page'] ?? 20)));

    $where_clauses = ["u.is_deleted = 1"];
    $params = [];

    if (!empty($search)) {
        $where_clauses[] = "(u.customer_id LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR a.account_no LIKE ?)";
        $s = "%{$search}%";
        $params = array_merge($params, [$s, $s, $s, $s, $s]);
    }

    if (!empty($date_from)) {
        $where_clauses[] = "DATE(u.deleted_at) >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $where_clauses[] = "DATE(u.deleted_at) <= ?";
        $params[] = $date_to;
    }

    if (!empty($account_type) && in_array(strtolower($account_type), ['savings', 'current'])) {
        $where_clauses[] = "a.account_type = ?";
        $params[] = strtolower($account_type);
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Count
    $count_sql = "
        SELECT COUNT(*) 
        FROM users u 
        LEFT JOIN accounts a ON u.customer_id = a.user_id 
        WHERE {$where_sql}
    ";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pages  = max(1, ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;

    // Fetch
    $stmt = $pdo->prepare("
        SELECT u.id, u.customer_id, u.full_name, u.email, u.phone, u.status, u.deleted_at, u.created_at, u.profile_picture,
               a.account_no, a.account_type, a.balance
        FROM users u
        LEFT JOIN accounts a ON u.customer_id = a.user_id
        WHERE {$where_sql}
        ORDER BY u.deleted_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    // Add avatar_url to each user
    $base_avatar_dir = dirname(__DIR__, 2) . '/uploads/avatars/';
    foreach ($users as &$u) {
        $avatar_path = $base_avatar_dir . 'user_' . $u['id'] . '.jpg';
        $u['avatar_url'] = file_exists($avatar_path) ? APP_URL . '/uploads/avatars/user_' . $u['id'] . '.jpg?t=' . filemtime($avatar_path) : null;
    }
    unset($u);

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
