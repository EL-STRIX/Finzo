<?php
/**
 * Finzo Banking System — Admin Branches API
 * GET endpoint: list all branches with staff counts.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $search = sanitize_input($_GET['search'] ?? '');
    $type   = sanitize_input($_GET['type'] ?? 'all');
    $status = sanitize_input($_GET['status'] ?? 'all');

    $where_clauses = ["1=1"];
    $params = [];

    if ($type !== 'all' && in_array($type, ['system', 'custom'])) {
        $where_clauses[] = "b.type = ?";
        $params[] = $type;
    }

    if ($status !== 'all' && in_array($status, ['active', 'inactive'])) {
        $where_clauses[] = "b.status = ?";
        $params[] = $status;
    }

    if (!empty($search)) {
        $where_clauses[] = "(b.name LIKE ? OR b.branch_code LIKE ? OR b.state LIKE ?)";
        $s = "%{$search}%";
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
    }

    $where_sql = implode(' AND ', $where_clauses);

    $stmt = $pdo->prepare("
        SELECT
            b.id, b.name, b.branch_code, b.state, b.description,
            b.type, b.status, b.cert_path, b.created_at,
            s.full_name as manager_name, s.staff_id as manager_staff_id,
            (SELECT COUNT(*) FROM staff st WHERE st.branch_id = b.id) as staff_count
        FROM branches b
        LEFT JOIN staff s ON b.manager_id = s.id
        WHERE {$where_sql}
        ORDER BY b.created_at DESC
    ");
    $stmt->execute($params);
    $branches = $stmt->fetchAll();

    json_response([
        'success'  => true,
        'branches' => $branches,
        'total'    => count($branches)
    ]);

} catch (PDOException $e) {
    error_log("Branches API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load branches'], 500);
}
