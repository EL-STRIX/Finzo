<?php
/**
 * Finzo Banking System — Admin Suspended Users API
 * GET endpoint: list of all suspended customer accounts with advanced filtering.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $search     = sanitize_input($_GET['search'] ?? '');
    $reason     = sanitize_input($_GET['reason'] ?? '');
    $start_date = sanitize_input($_GET['start_date'] ?? '');
    $end_date   = sanitize_input($_GET['end_date'] ?? '');
    $page       = max(1, intval($_GET['page'] ?? 1));
    $per_page   = min(50, max(10, intval($_GET['per_page'] ?? 20)));

    $where_clauses = ["u.status = 'suspended'", "u.is_deleted = 0"];
    $params = [];

    // Search
    if (!empty($search)) {
        $where_clauses[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR a.account_no LIKE ?)";
        $s = "%{$search}%";
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
    }

    // Reason filter (by audit log details)
    if (!empty($reason)) {
        $where_clauses[] = "l.details LIKE ?";
        $params[] = "%Reason: %{$reason}%";
    }

    // Date range filters
    if (!empty($start_date)) {
        $where_clauses[] = "DATE(COALESCE(l.created_at, u.updated_at)) >= ?";
        $params[] = $start_date;
    }
    if (!empty($end_date)) {
        $where_clauses[] = "DATE(COALESCE(l.created_at, u.updated_at)) <= ?";
        $params[] = $end_date;
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Count total with JOINs to audit log
    $count_sql = "
        SELECT COUNT(DISTINCT u.id)
        FROM users u
        LEFT JOIN accounts a ON u.customer_id = a.user_id
        LEFT JOIN (
            SELECT log1.entity_id, log1.details, log1.created_at, log1.actor_id
            FROM audit_log log1
            INNER JOIN (
                SELECT entity_id, MAX(id) as max_id
                FROM audit_log
                WHERE action_type = 'user_suspended' AND entity_type = 'user'
                GROUP BY entity_id
            ) log2 ON log1.id = log2.max_id
        ) l ON u.customer_id = l.entity_id
        WHERE {$where_sql}
    ";
    
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pages  = max(1, ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;

    // Fetch users with audit log details
    $sql = "
        SELECT u.id, u.customer_id, u.full_name, u.email, u.phone, u.status, u.created_at, u.updated_at,
               a.account_no, a.account_type, a.balance,
               COALESCE(l.details, 'Suspended') as raw_reason,
               COALESCE(l.created_at, u.updated_at) as raw_suspended_at,
               COALESCE(l.actor_id, 'Admin') as suspended_by
        FROM users u
        LEFT JOIN accounts a ON u.customer_id = a.user_id
        LEFT JOIN (
            SELECT log1.entity_id, log1.details, log1.created_at, log1.actor_id
            FROM audit_log log1
            INNER JOIN (
                SELECT entity_id, MAX(id) as max_id
                FROM audit_log
                WHERE action_type = 'user_suspended' AND entity_type = 'user'
                GROUP BY entity_id
            ) log2 ON log1.id = log2.max_id
        ) l ON u.customer_id = l.entity_id
        WHERE {$where_sql}
        ORDER BY raw_suspended_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    // Parse details to extract clean suspension reason and format date
    foreach ($users as &$u) {
        // Extract reason
        if (preg_match('/Reason:\s*(.*)$/i', $u['raw_reason'], $matches)) {
            $u['suspension_reason'] = trim($matches[1]);
        } else {
            $u['suspension_reason'] = 'Administrative Review';
        }
        
        // Format suspended_at date
        if ($u['raw_suspended_at']) {
            $d = new DateTime($u['raw_suspended_at']);
            $u['suspended_at'] = $d->format('d M Y h:i a');
        } else {
            $u['suspended_at'] = 'N/A';
        }
        
        // Duration (Always "Permanent" or calculated dynamically, standard is "Permanent" / "Eternal")
        $u['duration'] = 'Permanent';

        // Clean up raw database columns from output response
        unset($u['raw_reason']);
        unset($u['raw_suspended_at']);
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
    error_log("Suspended users API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load suspended users'], 500);
}
