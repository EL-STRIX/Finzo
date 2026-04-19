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
        $where_clauses[] = "al.action_type = ?";
        $params[] = $action_type;
    }

    if ($actor_role !== 'all' && in_array($actor_role, ['admin', 'staff', 'system'])) {
        $where_clauses[] = "al.actor_role = ?";
        $params[] = $actor_role;
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
        $where_clauses[] = "(al.action_type LIKE ? OR al.entity_id LIKE ? OR al.details LIKE ?)";
        $s = "%{$search}%";
        $params[] = $s;
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

    // Fetch
    $stmt = $pdo->prepare("
        SELECT al.id, al.action_type, al.entity_id, al.entity_type,
               al.actor_id, al.actor_role, al.details, al.ip_address, al.created_at
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

    json_response([
        'success'      => true,
        'entries'      => $entries,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page,
        'per_page'     => $per_page,
        'action_types' => $action_types
    ]);

} catch (PDOException $e) {
    error_log("Audit log API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load audit log'], 500);
}
