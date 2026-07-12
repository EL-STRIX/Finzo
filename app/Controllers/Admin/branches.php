<?php
/**
 * Finzo Banking System — Admin Branches API
 * GET endpoint: list all branches with staff counts.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $search = clean_input($_GET['search'] ?? '');
    $type   = clean_input($_GET['type'] ?? 'all');
    $status = clean_input($_GET['status'] ?? 'all');

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
            b.id, b.name, b.branch_code, b.state, b.city, b.address, b.description,
            b.type, b.status, b.cert_path, b.created_at,
            s.id as manager_id, s.full_name as manager_name, s.staff_id as manager_staff_id, s.personal_email as manager_email, s.profile_photo as manager_avatar,
            (SELECT COUNT(*) FROM staff st WHERE st.branch_id = b.id) as staff_count,
            (SELECT COUNT(*) FROM transactions t JOIN staff st ON t.processed_by_staff_id = st.id WHERE st.branch_id = b.id) as txn_count
        FROM branches b
        LEFT JOIN staff s ON b.manager_id = s.id
        WHERE {$where_sql}
        ORDER BY b.created_at DESC
    ");
    $stmt->execute($params);
    $branches = $stmt->fetchAll();

    // Calculate Stats
    $stmt_stats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
            (SELECT COUNT(*) FROM staff WHERE branch_id IS NOT NULL) as total_staff
        FROM branches
    ");
    $stats = $stmt_stats->fetch();

    foreach ($branches as &$b) {
        // Generate initials for the manager
        if (!empty($b['manager_name'])) {
            $parts = explode(' ', $b['manager_name']);
            $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
            $b['manager_initials'] = $initials;
        } else {
            $b['manager_initials'] = '??';
        }
    }

    json_response([
        'success' => true,
        'branches' => $branches,
        'stats' => [
            'total' => (int)$stats['total'],
            'active' => (int)$stats['active'],
            'total_staff' => (int)$stats['total_staff']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Branches API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load branches'], 500);
}
