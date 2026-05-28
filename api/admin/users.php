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
    $page         = max(1, intval($_GET['page'] ?? 1));
    $per_page     = min(50, max(10, intval($_GET['per_page'] ?? 20)));

    // Build query
    $where_clauses = ["u.is_deleted = 0", "u.status != 'suspended'"];
    $params = [];

    // Status filter
    if ($status !== 'all' && in_array($status, ['pending', 'under_review', 'active', 'rejected'])) {
        $where_clauses[] = "u.status = ?";
        $params[] = $status;
    }

    // Search
    if (!empty($search)) {
        $where_clauses[] = "(u.full_name LIKE ? OR u.customer_id LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Count total
    $count_sql = "
        SELECT COUNT(u.id)
        FROM users u
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
            u.id, u.customer_id, u.full_name, u.email, u.phone, u.status, u.created_at, u.last_login_at, u.profile_picture
        FROM users u
        WHERE {$where_sql}
        ORDER BY u.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    // Process profile pictures and avatars
    foreach ($users as &$u) {
        $profile_pic_url = $u['profile_picture'] ? APP_URL . '/' . $u['profile_picture'] : null;
        if (!$profile_pic_url) {
            $avatar_path = 'uploads/avatars/user_' . $u['id'] . '.jpg';
            if (file_exists(dirname(__DIR__, 2) . '/' . $avatar_path)) {
                $profile_pic_url = APP_URL . '/' . $avatar_path;
            }
        }
        $u['profile_picture_url'] = $profile_pic_url;
    }
    unset($u);

    // Stats
    $stats_stmt = $pdo->query("
        SELECT
            SUM(CASE WHEN is_deleted = 0 THEN 1 ELSE 0 END) as total_users,
            SUM(CASE WHEN is_deleted = 0 AND status = 'active' THEN 1 ELSE 0 END) as active_users,
            SUM(CASE WHEN is_deleted = 0 AND status = 'suspended' THEN 1 ELSE 0 END) as suspended_users,
            SUM(CASE WHEN is_deleted = 0 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as new_this_month,
            SUM(CASE WHEN is_deleted = 1 THEN 1 ELSE 0 END) as soft_deleted
        FROM users
    ");
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

    json_response([
        'success'      => true,
        'users'        => $users,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page,
        'per_page'     => $per_page,
        'stats'        => [
            'total_users'     => (int)($stats['total_users'] ?? 0),
            'active_users'    => (int)($stats['active_users'] ?? 0),
            'suspended_users' => (int)($stats['suspended_users'] ?? 0),
            'new_this_month'  => (int)($stats['new_this_month'] ?? 0),
            'soft_deleted'    => (int)($stats['soft_deleted'] ?? 0)
        ]
    ]);

} catch (PDOException $e) {
    error_log("Admin users API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load users'], 500);
}
