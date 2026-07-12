<?php
/**
 * Finzo Banking System — Admin Dashboard API
 * GET endpoint: returns all 15 stat card values and chart data.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    // 1. Total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_deleted = 0");
    $total_users = (int)$stmt->fetchColumn();

    // 2. Active users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active' AND is_deleted = 0");
    $active_users = (int)$stmt->fetchColumn();

    // 3. Pending users (pending + under_review)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status IN ('pending', 'under_review') AND is_deleted = 0");
    $pending_users = (int)$stmt->fetchColumn();

    // 4. Suspended users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'suspended' AND is_deleted = 0");
    $suspended_users = (int)$stmt->fetchColumn();

    // 5. Total staff
    $stmt = $pdo->query("SELECT COUNT(*) FROM staff");
    $total_staff = (int)$stmt->fetchColumn();

    // 6. Total branches
    $stmt = $pdo->query("SELECT COUNT(*) FROM branches");
    $total_branches = (int)$stmt->fetchColumn();

    // 7. Transactions today
    $stmt = $pdo->query("SELECT COUNT(*) FROM transactions WHERE DATE(created_at) = CURDATE()");
    $transactions_today = (int)$stmt->fetchColumn();

    // 8. Transactions this month
    $stmt = $pdo->query("SELECT COUNT(*) FROM transactions WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $transactions_month = (int)$stmt->fetchColumn();

    // 9. Deposits today
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'deposit' AND status = 'completed' AND DATE(created_at) = CURDATE()");
    $deposits_today = (float)$stmt->fetchColumn();

    // 10. Withdrawals today
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'withdrawal' AND status = 'completed' AND DATE(created_at) = CURDATE()");
    $withdrawals_today = (float)$stmt->fetchColumn();

    // 11. Transfers today
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'transfer' AND status = 'completed' AND DATE(created_at) = CURDATE()");
    $transfers_today = (float)$stmt->fetchColumn();

    // 12. Pending approvals (pending + under_review)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status IN ('pending', 'under_review') AND is_deleted = 0");
    $pending_approvals = (int)$stmt->fetchColumn();

    // 13. Locked accounts
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE locked_until > NOW() AND is_deleted = 0");
    $locked_accounts = (int)$stmt->fetchColumn();

    // 13a. Total accounts
    $stmt = $pdo->query("SELECT COUNT(*) FROM accounts WHERE status = 'active'");
    $total_accounts = (int)$stmt->fetchColumn();

    // 13b. Interest credited
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'interest_credit' AND status = 'completed'");
    $interest_credited = (float)$stmt->fetchColumn();

    // 13c. Soft deleted users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_deleted = 1");
    $soft_deleted_users = (int)$stmt->fetchColumn();

    // 14. Recent users (last 10) with profile picture
    $stmt = $pdo->query("
        SELECT id, full_name, email, status, created_at, profile_picture
        FROM users
        WHERE is_deleted = 0
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $recent_users = $stmt->fetchAll();

    foreach ($recent_users as &$u) {
        $profile_pic_url = $u['profile_picture'] ? APP_URL . '/' . $u['profile_picture'] : null;
        if (!$profile_pic_url) {
            $avatar_path = 'uploads/avatars/user_' . $u['id'] . '.jpg';
            if (file_exists(dirname(__DIR__, 3) . '/' . $avatar_path)) {
                $profile_pic_url = APP_URL . '/' . $avatar_path;
            }
        }
        $u['profile_picture_url'] = $profile_pic_url;
    }
    unset($u);

    // 15. Monthly chart data (last 6 months transaction volumes)
    $monthly_chart_data = [];
    for ($i = 5; $i >= 0; $i--) {
        $month_date = date('Y-m', strtotime("-{$i} months"));
        $month_label = date('M Y', strtotime("-{$i} months"));

        $stmt = $pdo->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as deposits,
                COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as withdrawals,
                COALESCE(SUM(CASE WHEN type = 'transfer' THEN amount ELSE 0 END), 0) as transfers,
                COUNT(*) as count
            FROM transactions
            WHERE status = 'completed'
                AND DATE_FORMAT(created_at, '%Y-%m') = ?
        ");
        $stmt->execute([$month_date]);
        $month_data = $stmt->fetch();

        $monthly_chart_data[] = [
            'month'       => $month_label,
            'deposits'    => (float)$month_data['deposits'],
            'withdrawals' => (float)$month_data['withdrawals'],
            'transfers'   => (float)$month_data['transfers'],
            'count'       => (int)$month_data['count']
        ];
    }

    // Total system balance
    $stmt = $pdo->query("SELECT COALESCE(SUM(balance), 0) FROM accounts WHERE status = 'active'");
    $total_balance = (float)$stmt->fetchColumn();

    // Fetch fresh admin data
    $stmt = $pdo->prepare("SELECT full_name, email, profile_photo, admin_role FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin_data = $stmt->fetch();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    json_response([
        'success' => true,
        'csrf_token' => $_SESSION['csrf_token'],
        'admin' => [
            'id' => $_SESSION['admin_id'],
            'full_name' => $admin_data['full_name'],
            'email' => $admin_data['email'],
            'profile_photo' => $admin_data['profile_photo'],
            'role' => $admin_data['admin_role']
        ],
        'stats' => [
            'total_users'        => $total_users,
            'active_users'       => $active_users,
            'pending_users'      => $pending_users,
            'suspended_users'    => $suspended_users,
            'total_staff'        => $total_staff,
            'total_branches'     => $total_branches,
            'transactions_today' => $transactions_today,
            'transactions_month' => $transactions_month,
            'deposits_today'     => $deposits_today,
            'withdrawals_today'  => $withdrawals_today,
            'transfers_today'    => $transfers_today,
            'pending_approvals'  => $pending_approvals,
            'locked_accounts'    => $locked_accounts,
            'total_balance'      => $total_balance,
            'total_accounts'     => $total_accounts,
            'interest_credited'  => $interest_credited,
            'soft_deleted_users' => $soft_deleted_users
        ],
        'recent_users'       => $recent_users,
        'monthly_chart_data' => $monthly_chart_data
    ]);

} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load dashboard data'], 500);
}
