<?php
/**
 * Finzo Banking System — Admin Pending Approvals API
 * GET endpoint: list of KYC applications pending review.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $status_filter = sanitize_input($_GET['status'] ?? 'all');
    $search        = sanitize_input($_GET['search'] ?? '');
    $page          = max(1, intval($_GET['page'] ?? 1));
    $per_page      = min(50, max(10, intval($_GET['per_page'] ?? 20)));

    $where_clauses = ["u.is_deleted = 0"];
    $params = [];

    // Status filter: all pending/under_review, or specific
    if ($status_filter === 'pending') {
        $where_clauses[] = "u.status = 'pending'";
    } elseif ($status_filter === 'under_review') {
        $where_clauses[] = "u.status = 'under_review'";
    } else {
        // Default: show both pending and under_review
        $where_clauses[] = "u.status IN ('pending', 'under_review')";
    }

    if (!empty($search)) {
        $where_clauses[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
        $s = "%{$search}%";
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
        SELECT u.id, u.full_name, u.email, u.phone, u.status, u.kyc_submitted_at, u.created_at,
               a.account_type, a.balance as initial_deposit,
               k.application_no, k.submission_count, k.submitted_at
        FROM users u
        LEFT JOIN accounts a ON u.id = a.user_id
        LEFT JOIN kyc_applications k ON u.id = k.user_id
        WHERE {$where_sql}
        ORDER BY u.kyc_submitted_at DESC, u.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $applications = $stmt->fetchAll();

    json_response([
        'success'      => true,
        'applications' => $applications,
        'total'        => $total,
        'pages'        => $pages,
        'current_page' => $page
    ]);

} catch (PDOException $e) {
    error_log("Pending approvals API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load pending approvals'], 500);
}
