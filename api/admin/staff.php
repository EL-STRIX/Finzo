<?php
/**
 * api/admin/staff.php
 * GET endpoint: returns a paginated list of staff members with branch info.
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $search    = sanitize_input($_GET['search'] ?? '');
    $status    = sanitize_input($_GET['status'] ?? 'all');
    $branch_id = intval($_GET['branch_id'] ?? 0);
    $page      = max(1, intval($_GET['page'] ?? 1));
    $per_page  = 20; // As per spec

    $where_clauses = ["1=1"];
    $params = [];

    if ($status !== 'all' && in_array($status, ['active', 'suspended'])) {
        $where_clauses[] = "s.status = ?";
        $params[] = $status;
    }

    if ($branch_id > 0) {
        $where_clauses[] = "s.branch_id = ?";
        $params[] = $branch_id;
    }

    if (!empty($search)) {
        $where_clauses[] = "(s.full_name LIKE ? OR s.personal_email LIKE ? OR s.staff_id LIKE ? OR s.phone LIKE ?)";
        $s = "%{$search}%";
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Get Total for Pagination
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff s WHERE {$where_sql}");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pages  = max(1, ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;

    // Fetch Staff List
    $stmt = $pdo->prepare("
        SELECT
            s.id, s.staff_id, s.full_name, s.personal_email,
            s.phone, s.designation, s.department,
            s.status, s.profile_photo,
            s.date_of_joining, s.last_login_at,
            b.name as branch_name
        FROM staff s
        LEFT JOIN branches b ON b.id = s.branch_id
        WHERE {$where_sql}
        ORDER BY s.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $staff_list = $stmt->fetchAll();

    // Format for frontend
    $base_url = APP_URL . '/';
    foreach ($staff_list as &$s) {
        if ($s['profile_photo']) {
            $s['profile_photo_url'] = $base_url . $s['profile_photo'];
        }
        $name_parts = explode(' ', $s['full_name']);
        $s['initials'] = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
    }

    json_response([
        'success'      => true,
        'staff'        => $staff_list,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page
    ]);

} catch (PDOException $e) {
    error_log("Admin staff list error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load staff list'], 500);
}
