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
    $status_filter = sanitize_input($_GET['status'] ?? '');
    $page    = max(1, intval($_GET['page'] ?? 1));
    $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

    $where_clauses = ["u.is_deleted = 0"];
    $params = [];

    // Base logic for locked accounts
    if ($tab === 'locked') {
        $where_clauses[] = "u.temp_pass_status IN ('pending', 'done')";
    } elseif ($tab === 'flagged') {
        // Flagged: high failed attempts but not locked
        $where_clauses[] = "u.failed_attempts >= 3 AND u.temp_pass_status = 'none'";
    } else {
        $where_clauses[] = "(u.temp_pass_status IN ('pending', 'done') OR u.failed_attempts >= 3)";
    }

    // Status filter
    if ($status_filter === 'pending') {
        $where_clauses[] = "u.temp_pass_status = 'pending'";
    } elseif ($status_filter === 'done') {
        $where_clauses[] = "u.temp_pass_status = 'done'";
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
        SELECT u.id, u.full_name, u.email, u.phone, u.status, u.profile_picture,
               u.failed_attempts, u.locked_until, u.last_login_at, u.last_login_ip,
               u.updated_at, u.temp_pass_status,
               a.account_no, a.account_type
        FROM users u
        LEFT JOIN accounts a ON u.customer_id = a.user_id
        WHERE {$where_sql}
        ORDER BY u.locked_until DESC, u.failed_attempts DESC
        LIMIT {$per_page} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $accounts = $stmt->fetchAll();

    $formatted_accounts = [];
    foreach ($accounts as $acc) {
        // Locked at is approximately updated_at
        $locked_timestamp = strtotime($acc['updated_at']);
        $acc['locked_at'] = date('d M Y', $locked_timestamp);
        $acc['lock_time'] = date('h:i A', $locked_timestamp);
        
        // Pass the temp_pass_status explicitly
        $acc['temp_pass_status'] = $acc['temp_pass_status'] ?? 'pending';

        if ($acc['temp_pass_status'] === 'done') {
            $acc['status_text'] = 'Done';
            $acc['status_badge'] = 'badge-success';
        } else {
            $acc['status_text'] = 'Pending';
            $acc['status_badge'] = 'badge-warn';
        }
        $formatted_accounts[] = $acc;
    }

    json_response([
        'success'      => true,
        'accounts'     => $formatted_accounts,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page
    ]);

} catch (PDOException $e) {
    error_log("Locked accounts API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load locked accounts'], 500);
}
