<?php
/**
 * Finzo Banking System — Admin Suspended Staff API
 * GET endpoint: list of all suspended staff members.
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

    $where_clauses = ["s.status = 'suspended'"];
    $params = [];

    if (!empty($search)) {
        $where_clauses[] = "(s.full_name LIKE ? OR s.email LIKE ? OR s.staff_id LIKE ?)";
        $s = "%{$search}%";
        $params = [$s, $s, $s];
    }

    $where_sql = implode(' AND ', $where_clauses);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff s WHERE {$where_sql}");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pages  = max(1, ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;

    $stmt = $pdo->prepare("
        SELECT s.id, s.staff_id, s.full_name, s.email, s.phone, s.role, s.status,
               s.created_at, s.updated_at,
               b.name as branch_name
        FROM staff s
        LEFT JOIN branches b ON s.branch_id = b.id
        WHERE {$where_sql}
        ORDER BY s.updated_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $staff_list = $stmt->fetchAll();

    // Format list for frontend
    foreach ($staff_list as &$s) {
        $name_parts = explode(' ', $s['full_name']);
        $s['initials'] = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
        $s['suspended_at'] = date('d M Y, H:i', strtotime($s['updated_at']));
        $s['suspension_reason'] = 'Administrative Review';
        $s['suspended_by'] = 'Admin';
        $s['duration'] = 'Indefinite';
    }

    json_response([
        'status'  => 'success',
        'data'    => [
            'staff'        => $staff_list,
            'total'        => $total,
            'pages'        => $pages,
            'current_page' => $page
        ]
    ]);

} catch (PDOException $e) {
    error_log("Suspended staff API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load suspended staff'], 500);
}
