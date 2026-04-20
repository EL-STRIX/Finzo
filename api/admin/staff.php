<?php
/**
 * Finzo Banking System — Admin Staff List API
 * GET endpoint: paginated staff list with search and filters.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    // Action: Get Next Staff ID
    if (isset($_GET['action']) && $_GET['action'] === 'next_id') {
        require_once dirname(__DIR__, 2) . '/config/functions.php';
        $next_id = generate_staff_id($pdo);
        json_response(['success' => true, 'next_id' => $next_id]);
    }

    $search    = sanitize_input($_GET['search'] ?? '');
    $status    = sanitize_input($_GET['status'] ?? 'all');
    $branch_id = intval($_GET['branch_id'] ?? 0);
    $page      = max(1, intval($_GET['page'] ?? 1));
    $per_page  = min(50, max(10, intval($_GET['per_page'] ?? 20)));

    $where_clauses = [];
    $params = [];

    if ($status !== 'all' && in_array($status, ['active', 'suspended'])) {
        $where_clauses[] = "s.status = ?";
        $params[] = $status;
    } else {
        // By default, exclude suspended staff from the main list
        $where_clauses[] = "s.status != 'suspended'";
    }

    if ($branch_id > 0) {
        $where_clauses[] = "s.branch_id = ?";
        $params[] = $branch_id;
    }

    if (!empty($search)) {
        $where_clauses[] = "(s.full_name LIKE ? OR s.email LIKE ? OR s.staff_id LIKE ? OR s.phone LIKE ?)";
        $s = "%{$search}%";
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
    }

    $where_sql = implode(' AND ', $where_clauses);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff s WHERE {$where_sql}");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pages  = max(1, ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;

    // Fetch Stats
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
        COUNT(CASE WHEN status = 'suspended' THEN 1 END) as suspended
    FROM staff");
    $stats = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT s.id, s.staff_id, s.full_name, s.email, s.phone, s.role, s.status,
               s.last_login_at as last_active, s.created_at,
               b.name as branch_name, b.branch_code
        FROM staff s
        LEFT JOIN branches b ON s.branch_id = b.id
        WHERE {$where_sql}
        ORDER BY s.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $staff_list = $stmt->fetchAll();

    // Format list for frontend
    foreach ($staff_list as &$s) {
        $name_parts = explode(' ', $s['full_name']);
        $s['name'] = $s['full_name'];
        $s['initials'] = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
        $s['designation'] = ucfirst($s['role']);
        $s['last_active'] = $s['last_active'] ? date('d M, H:i', strtotime($s['last_active'])) : 'Never';
        $s['volume_formatted'] = '₹0'; // Placeholder for transaction logic
        $s['transactions_today'] = 0; // Placeholder
    }

    json_response([
        'success'      => true,
        'staff'        => $staff_list,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page,
        'stats'        => [
            'total'    => $stats['total'],
            'active'   => $stats['active'],
            'suspended' => $stats['suspended'],
            'transactions_today' => 0
        ]
    ]);

} catch (PDOException $e) {
    error_log("Admin staff list error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load staff list'], 500);
}
