<?php
/**
 * Finzo Banking System — Admin Pending Approvals API
 * GET endpoint: list of KYC applications pending review.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $status_filter = clean_input($_GET['status'] ?? 'all');
    $search        = clean_input($_GET['search'] ?? '');
    $page          = max(1, intval($_GET['page'] ?? 1));
    $per_page      = min(50, max(10, intval($_GET['per_page'] ?? 10)));

    $where_clauses = ["u.is_deleted = 0"];
    $params = [];

    // Status filter
    if ($status_filter === 'pending') {
        $where_clauses[] = "u.status = 'pending'";
    } elseif ($status_filter === 'under_review') {
        $where_clauses[] = "u.status = 'under_review'";
    } else {
        // Default: show both pending and under_review
        $where_clauses[] = "u.status IN ('pending', 'under_review')";
    }

    if (!empty($search)) {
        $where_clauses[] = "(u.full_name LIKE ? OR u.customer_id LIKE ?)";
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
        SELECT u.id, u.customer_id, u.full_name, u.email, u.phone, u.status,
               u.kyc_submitted_at, u.created_at, u.profile_picture,
               a.account_type,
               k.application_no, k.submission_count, k.submitted_at,
               SUBSTRING(u.full_name, 1, 1) as initials,
               CASE u.status
                   WHEN 'pending'      THEN 'Pending Review'
                   WHEN 'under_review' THEN 'Under Review'
                   ELSE u.status
               END as status_text,
               k.id_type
        FROM users u
        LEFT JOIN accounts a ON u.customer_id = a.user_id
        LEFT JOIN kyc_applications k ON u.customer_id = k.user_id
        WHERE {$where_sql}
        ORDER BY u.kyc_submitted_at DESC, u.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($applications as &$app) {
        $profile_pic_url = $app['profile_picture'] ? APP_URL . '/' . $app['profile_picture'] : null;
        if (!$profile_pic_url) {
            $avatar_path = 'uploads/avatars/user_' . $app['id'] . '.jpg';
            if (file_exists(dirname(__DIR__, 3) . '/' . $avatar_path)) {
                $profile_pic_url = APP_URL . '/' . $avatar_path;
            }
        }
        $app['profile_picture_url'] = $profile_pic_url;
    }

    // Stats
    $stats_stmt = $pdo->query("
        SELECT
            SUM(CASE WHEN status = 'pending'      THEN 1 ELSE 0 END) as pending_review,
            SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) as under_review,
            SUM(CASE WHEN status = 'active' AND DATE(kyc_submitted_at) = CURDATE() THEN 1 ELSE 0 END) as approved_today,
            SUM(CASE WHEN status = 'rejected' AND DATE(kyc_submitted_at) = CURDATE() THEN 1 ELSE 0 END) as rejected_today,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as total_approved
        FROM users WHERE is_deleted = 0
    ");
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

    json_response([
        'success' => true,
        'data'    => [
            'applications' => $applications,
            'total'        => $total,
            'pages'        => $pages,
            'current_page' => $page,
            'stats'        => [
                'pending_review'  => (int)($stats['pending_review']  ?? 0),
                'under_review'    => (int)($stats['under_review']    ?? 0),
                'approved_today'  => (int)($stats['approved_today']  ?? 0),
                'rejected_today'  => (int)($stats['rejected_today']  ?? 0),
                'total_approved'  => (int)($stats['total_approved']  ?? 0),
            ]
        ]
    ]);

} catch (PDOException $e) {
    error_log("Pending approvals API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load pending approvals'], 500);
}
