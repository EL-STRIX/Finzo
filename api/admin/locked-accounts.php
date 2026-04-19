<?php
/**
 * Finzo Banking System — Admin Locked Accounts API
 * GET endpoint: accounts locked due to failed login attempts.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $tab     = sanitize_input($_GET['tab'] ?? 'locked');
    $search  = sanitize_input($_GET['search'] ?? '');
    $page    = max(1, intval($_GET['page'] ?? 1));
    $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

    $where_clauses = ["u.is_deleted = 0"];
    $params = [];

    if ($tab === 'locked') {
        $where_clauses[] = "u.locked_until > NOW()";
    } elseif ($tab === 'flagged') {
        // Flagged: high failed attempts but not currently locked
        $where_clauses[] = "u.failed_attempts >= 3 AND (u.locked_until IS NULL OR u.locked_until <= NOW())";
    } else {
        // All with any failed attempts
        $where_clauses[] = "(u.locked_until > NOW() OR u.failed_attempts >= 3)";
    }

    if (!empty($search)) {
        $where_clauses[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
        $s = "%{$search}%";
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
    }

    $where_sql = implode(' AND ', $where_clauses);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users u WHERE {$where_sql}");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pages  = max(1, ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;

    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, u.phone, u.status,
               u.failed_attempts, u.locked_until, u.last_login_at, u.last_login_ip,
               a.account_no
        FROM users u
        LEFT JOIN accounts a ON u.id = a.user_id
        WHERE {$where_sql}
        ORDER BY u.locked_until DESC, u.failed_attempts DESC
        LIMIT {$per_page} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $accounts = $stmt->fetchAll();

    json_response([
        'success'      => true,
        'accounts'     => $accounts,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page
    ]);

} catch (PDOException $e) {
    error_log("Locked accounts API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load locked accounts'], 500);
}
