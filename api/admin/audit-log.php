<?php
/**
 * Finzo Banking System — Admin Audit Log API
 * GET endpoint: paginated audit log entries.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $search      = sanitize_input($_GET['search'] ?? '');
    $action_type = sanitize_input($_GET['action_type'] ?? 'all');
    $actor_role  = sanitize_input($_GET['actor_role'] ?? 'all');
    $from_date   = sanitize_input($_GET['from_date'] ?? '');
    $to_date     = sanitize_input($_GET['to_date'] ?? '');
    $page        = max(1, intval($_GET['page'] ?? 1));
    $per_page    = min(100, max(10, intval($_GET['per_page'] ?? 25)));

    $where_clauses = ["1=1"];
    $params = [];

    if ($action_type !== 'all') {
        $where_clauses[] = "al.action_type LIKE ?";
        $params[] = "%{$action_type}%";
    }

    if ($actor_role !== 'all' && in_array($actor_role, ['admin', 'staff', 'system', 'user'])) {
        if ($actor_role === 'admin') {
            $where_clauses[] = "(al.actor_role LIKE '%Admin%' OR al.actor_role = 'Moderator' OR al.actor_role IN ('admin', 'super_admin', 'moderator'))";
        } else if ($actor_role === 'staff') {
            $where_clauses[] = "al.actor_role IN ('Staff', 'staff')";
        } else if ($actor_role === 'system') {
            $where_clauses[] = "al.actor_role IN ('System', 'system')";
        } else if ($actor_role === 'user') {
            $where_clauses[] = "al.actor_role IN ('User', 'user')";
        }
    }

    if (!empty($from_date)) {
        $where_clauses[] = "DATE(al.created_at) >= ?";
        $params[] = $from_date;
    }

    if (!empty($to_date)) {
        $where_clauses[] = "DATE(al.created_at) <= ?";
        $params[] = $to_date;
    }

    if (!empty($search)) {
        $where_clauses[] = "(al.actor_id LIKE ? OR al.entity_id LIKE ?)";
        $s = "%{$search}%";
        $params[] = $s;
        $params[] = $s;
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_log al WHERE {$where_sql}");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pages  = max(1, ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;

    $stmt = $pdo->prepare("
        SELECT al.id, al.action_type, al.entity_id, al.entity_type,
               al.actor_id, al.actor_role, al.details, al.ip_address, al.created_at,
               al.actor_id as actor_display_id,
               al.actor_role as actor_specific_role,
               CASE 
                   WHEN al.entity_type = 'user' THEN COALESCE((SELECT customer_id FROM users WHERE id = al.entity_id OR customer_id = al.entity_id LIMIT 1), al.entity_id)
                   WHEN al.entity_type = 'staff' THEN COALESCE((SELECT staff_id FROM staff WHERE id = al.entity_id OR staff_id = al.entity_id LIMIT 1), al.entity_id)
                   WHEN al.entity_type = 'admin' THEN COALESCE((SELECT admin_id FROM admin_users WHERE id = al.entity_id OR admin_id = al.entity_id LIMIT 1), al.entity_id)
                   ELSE al.entity_id 
               END as entity_display_id
        FROM audit_log al
        WHERE {$where_sql}
        ORDER BY al.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $entries = $stmt->fetchAll();

    // Get distinct action types for filter dropdown
    $stmt = $pdo->query("SELECT DISTINCT action_type FROM audit_log ORDER BY action_type");
    $action_types = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Compute exact counts based on current filters
    $stmt_stats = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN al.actor_role LIKE '%Admin%' OR al.actor_role = 'Moderator' OR al.actor_role IN ('admin', 'super_admin', 'moderator') THEN 1 ELSE 0 END) as admin_count,
            SUM(CASE WHEN al.actor_role IN ('Staff', 'staff') THEN 1 ELSE 0 END) as staff_count,
            SUM(CASE WHEN al.actor_role IN ('User', 'user') THEN 1 ELSE 0 END) as user_count
        FROM audit_log al
        WHERE {$where_sql}
    ");
    $stmt_stats->execute($params);
    $stats = $stmt_stats->fetch();

    json_response([
        'success'      => true,
        'entries'      => $entries,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page,
        'per_page'     => $per_page,
        'action_types' => $action_types,
        'stats'        => [
            'admin'  => (int)$stats['admin_count'],
            'staff'  => (int)$stats['staff_count'],
            'user'   => (int)$stats['user_count']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Audit log API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load audit log'], 500);
}
