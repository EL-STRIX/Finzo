<?php

namespace App\Application\Services;

use App\Shared\Security\SecurityService;

use App\Infrastructure\Repositories\AdminOperationsRepository;

class AdminOperationsService
{
    protected SecurityService $security;

    protected AdminOperationsRepository $repository;

    public function __construct(AdminOperationsRepository $repository, ?SecurityService $security = null)
    {
        $this->security = $security ?? new \App\Shared\Security\SecurityService();
        $this->repository = $repository;
    }

    public function getAuditLog(array $filters, int $page, int $perPage): array
    {
        $where_clauses = ["1=1"];
        $params = [];

        if ($filters['action_type'] !== 'all') {
            $where_clauses[] = "al.action_type LIKE ?";
            $params[] = "%{$filters['action_type']}%";
        }

        if ($filters['actor_role'] !== 'all' && in_array($filters['actor_role'], ['admin', 'staff', 'system', 'user'])) {
            if ($filters['actor_role'] === 'admin') {
                $where_clauses[] = "(al.actor_role LIKE '%Admin%' OR al.actor_role = 'Moderator' OR al.actor_role IN ('admin', 'super_admin', 'moderator'))";
            } elseif ($filters['actor_role'] === 'staff') {
                $where_clauses[] = "al.actor_role IN ('Staff', 'staff')";
            } elseif ($filters['actor_role'] === 'system') {
                $where_clauses[] = "al.actor_role IN ('System', 'system')";
            } elseif ($filters['actor_role'] === 'user') {
                $where_clauses[] = "al.actor_role IN ('User', 'user')";
            }
        }

        if (!empty($filters['from_date'])) {
            $where_clauses[] = "DATE(al.created_at) >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where_clauses[] = "DATE(al.created_at) <= ?";
            $params[] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $where_clauses[] = "(al.actor_id LIKE ? OR al.entity_id LIKE ?)";
            $s = "%{$filters['search']}%";
            $params[] = $s;
            $params[] = $s;
        }

        $where_sql = implode(' AND ', $where_clauses);
        $total = $this->repository->countAuditLogs($where_sql, $params);

        $pages  = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $entries = $this->repository->getAuditLogs($where_sql, $params, $perPage, $offset);
        $action_types = $this->repository->getAuditLogActionTypes();
        $stats = $this->repository->getAuditLogStats($where_sql, $params);

        return [
            'entries'      => $entries,
            'total'        => $total,
            'pages'        => $pages,
            'current_page' => $page,
            'per_page'     => $perPage,
            'action_types' => $action_types,
            'stats'        => [
                'admin'  => (int)$stats['admin_count'],
                'staff'  => (int)$stats['staff_count'],
                'user'   => (int)$stats['user_count']
            ]
        ];
    }

    public function getDashboardData(int $adminId): array
    {
        $stats = [
            'total_users'        => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM users WHERE is_deleted = 0"),
            'active_users'       => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM users WHERE status = 'active' AND is_deleted = 0"),
            'pending_users'      => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM users WHERE status IN ('pending', 'under_review') AND is_deleted = 0"),
            'suspended_users'    => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM users WHERE status = 'suspended' AND is_deleted = 0"),
            'total_staff'        => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM staff"),
            'total_branches'     => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM branches"),
            'transactions_today' => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM transactions WHERE DATE(created_at) = CURDATE()"),
            'transactions_month' => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM transactions WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"),
            'deposits_today'     => (float)$this->repository->getSingleStat("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'deposit' AND status = 'completed' AND DATE(created_at) = CURDATE()"),
            'withdrawals_today'  => (float)$this->repository->getSingleStat("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'withdrawal' AND status = 'completed' AND DATE(created_at) = CURDATE()"),
            'transfers_today'    => (float)$this->repository->getSingleStat("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'transfer' AND status = 'completed' AND DATE(created_at) = CURDATE()"),
            'pending_approvals'  => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM users WHERE status IN ('pending', 'under_review') AND is_deleted = 0"),
            'locked_accounts'    => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM users WHERE locked_until > NOW() AND is_deleted = 0"),
            'total_balance'      => (float)$this->repository->getSingleStat("SELECT COALESCE(SUM(balance), 0) FROM accounts WHERE status = 'active'"),
            'total_accounts'     => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM accounts WHERE status = 'active'"),
            'interest_credited'  => (float)$this->repository->getSingleStat("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'interest_credit' AND status = 'completed'"),
            'soft_deleted_users' => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM users WHERE is_deleted = 1")
        ];

        $recent_users = $this->repository->getDashboardRecentUsers();
        foreach ($recent_users as &$u) {
            $profile_pic_url = $u['profile_picture'] ? APP_URL . '/' . $u['profile_picture'] : null;
            if (!$profile_pic_url) {
                $avatar_path = 'uploads/avatars/user_' . $u['id'] . '.jpg';
                if (file_exists(FINZO_ROOT . '/' . $avatar_path)) {
                    $profile_pic_url = APP_URL . '/' . $avatar_path;
                }
            }
            $u['profile_picture_url'] = $profile_pic_url;
        }
        unset($u);

        $monthly_chart_data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month_date = date('Y-m', strtotime("-{$i} months"));
            $month_label = date('M Y', strtotime("-{$i} months"));
            $month_data = $this->repository->getMonthlyChartDataRow($month_date);

            $monthly_chart_data[] = [
                'month'       => $month_label,
                'deposits'    => (float)$month_data['deposits'],
                'withdrawals' => (float)$month_data['withdrawals'],
                'transfers'   => (float)$month_data['transfers'],
                'count'       => (int)$month_data['count']
            ];
        }

        $admin_data = $this->repository->getAdminById($adminId);

        return [
            'stats' => $stats,
            'recent_users' => $recent_users,
            'monthly_chart_data' => $monthly_chart_data,
            'admin' => $admin_data
        ];
    }

    public function getDeletedUsers(array $filters, int $page, int $perPage): array
    {
        $where_clauses = ["u.is_deleted = 1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where_clauses[] = "(u.customer_id LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR a.account_no LIKE ?)";
            $s = "%{$filters['search']}%";
            $params = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        if (!empty($filters['date_from'])) {
            $where_clauses[] = "DATE(u.deleted_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where_clauses[] = "DATE(u.deleted_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['account_type']) && in_array(strtolower($filters['account_type']), ['savings', 'current'])) {
            $where_clauses[] = "a.account_type = ?";
            $params[] = strtolower($filters['account_type']);
        }

        $where_sql = implode(' AND ', $where_clauses);
        $total = $this->repository->countDeletedUsers($where_sql, $params);

        $pages  = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $users = $this->repository->getDeletedUsers($where_sql, $params, $perPage, $offset);

        $base_avatar_dir = FINZO_ROOT . '/uploads/avatars/';
        foreach ($users as &$u) {
            $avatar_path = $base_avatar_dir . 'user_' . $u['id'] . '.jpg';
            $u['avatar_url'] = file_exists($avatar_path) ? APP_URL . '/uploads/avatars/user_' . $u['id'] . '.jpg?t=' . filemtime($avatar_path) : null;
        }
        unset($u);

        return [
            'users' => $users,
            'total' => $total,
            'pages' => $pages,
            'current_page' => $page
        ];
    }

    public function getInterestData(): array
    {
        $current_rate_row = $this->repository->getCurrentInterestRate();
        $current_rate = $current_rate_row ? (float)$current_rate_row['rate'] : 4.25;
        $last_change_date = $current_rate_row ? date('d M Y', strtotime($current_rate_row['created_at'])) : '-';

        $total_interest = (float)$this->repository->getSingleStat("SELECT SUM(amount) FROM transactions WHERE type = 'interest_credit' AND status = 'completed'");
        $stats = [
            'total_interest_credited' => '₹' . number_format($total_interest, 2),
            'earning_accounts' => (int)$this->repository->getSingleStat("SELECT COUNT(*) FROM accounts WHERE account_type = 'savings' AND status = 'active'"),
            'interest_delta' => '+0%'
        ];

        $this_month = (float)$this->repository->getSingleStat("SELECT SUM(amount) FROM transactions WHERE type = 'interest_credit' AND status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $last_month = (float)$this->repository->getSingleStat("SELECT SUM(amount) FROM transactions WHERE type = 'interest_credit' AND status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)");

        if ($last_month > 0) {
            $delta = (($this_month - $last_month) / $last_month) * 100;
            $stats['interest_delta'] = ($delta > 0 ? '+' : '') . round($delta, 1) . '%';
        }

        $chart_data = [];
        for ($i = 11; $i >= 0; $i--) {
            $chart_data[] = (float)$this->repository->getSingleStat("SELECT SUM(amount) FROM transactions WHERE type = 'interest_credit' AND status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL {$i} MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL {$i} MONTH)");
        }

        $history_rows = $this->repository->getInterestHistory();
        $history = [];
        $first = true;
        foreach ($history_rows as $row) {
            $status = 'Past';
            if ($row['apply_from_date'] > date('Y-m-d')) {
                $status = 'Upcoming';
            } elseif ($first) {
                $status = 'Active';
                $first = false;
            }
            $history[] = [
                'effective_date' => date('d M Y', strtotime($row['apply_from_date'] ?? $row['created_at'])),
                'rate' => (float)$row['rate'],
                'updated_by' => $row['changed_by_name'],
                'status' => $status
            ];
        }

        return [
            'current_rate'     => $current_rate,
            'last_change_date' => $last_change_date,
            'stats'            => $stats,
            'chart_data'       => $chart_data,
            'history'          => $history
        ];
    }

    public function updateInterestRate(float $new_rate, string $apply_from, string $custom_date, int $adminId): array
    {
        if ($new_rate < 0.01 || $new_rate > 99.99) {
            return ['success' => false, 'errors' => ['new_rate' => 'Interest rate must be between 0.01% and 99.99%']];
        }

        $valid_types = ['Immediately', 'Next Month', 'Custom Date'];
        if (!in_array($apply_from, $valid_types)) {
            return ['success' => false, 'errors' => ['apply_from' => 'Invalid application type']];
        }

        $apply_date = null;
        if ($apply_from === 'Custom Date') {
            if (empty($custom_date)) {
                return ['success' => false, 'errors' => ['custom_date' => 'Custom date is required']];
            } else {
                $apply_date = $custom_date;
            }
        } elseif ($apply_from === 'Next Month') {
            $apply_date = date('Y-m-01', strtotime('first day of next month'));
        } else {
            $apply_date = date('Y-m-d');
        }

        $adminData = $this->repository->getAdminById($adminId);
        if (!$adminData || !$adminData['admin_id']) {
            return ['success' => false, 'message' => 'Admin account not found', 'code' => 404];
        }
        $admin_display_id = $adminData['admin_id'];

        $this->repository->insertInterestRate($new_rate, $apply_from, $apply_date, $admin_display_id);

        if ($apply_from === 'Immediately') {
            $this->repository->updateActiveSavingsInterestRate($new_rate);
            $savings_accounts = $this->repository->getActiveSavingsAccounts();

            foreach ($savings_accounts as $acc) {
                $balance = (float)$acc['balance'];
                $interest_amount = round($balance * ($new_rate / 100) / 12, 2);

                if ($interest_amount >= 0.01) {
                    $new_balance = $balance + $interest_amount;
                    $txn_id = 'TXN' . strtoupper(bin2hex(random_bytes(8)));

                    $this->repository->addTransaction(
                        $txn_id,
                        null,
                        $acc['id'],
                        'interest_credit',
                        $interest_amount,
                        $new_balance,
                        "Savings Interest Credit",
                        "Interest credited at " . number_format($new_rate, 2) . "% p.a.",
                        'completed'
                    );

                    $this->repository->updateAccountBalance($new_balance, $acc['id']);

                    $notifMessage = "₹" . number_format($interest_amount, 2) . " interest has been credited to your savings account. New balance: ₹" . number_format($new_balance, 2);
                    $this->repository->addNotification($acc['user_id'], 'transaction', 'Interest Credited', $notifMessage);
                }
            }
        }

        // Global function from config/functions.php
        global $pdo;
        if (function_exists('write_audit_log') && isset($pdo)) {
            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('interest_rate_updated', null, 'system', $adminId, 'admin', "Interest rate changed to {$new_rate}%. Apply from: {$apply_from}" . ($apply_date ? " ({$apply_date})" : ''))
);
        }

        return ['success' => true, 'message' => "Interest rate updated to {$new_rate}%"];
    }

    public function exportUsers(array $filters): array
    {
        $where_clauses = ["u.is_deleted = 0", "u.status != 'suspended'"];
        $params = [];

        if ($filters['status'] !== 'all' && in_array($filters['status'], ['pending', 'under_review', 'active', 'rejected'])) {
            $where_clauses[] = "u.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where_clauses[] = "(u.full_name LIKE ? OR u.customer_id LIKE ?)";
            $search_param = "%{$filters['search']}%";
            $params[] = $search_param;
            $params[] = $search_param;
        }

        $where_sql = implode(' AND ', $where_clauses);

        return $this->repository->getExportUsers($where_sql, $params);
    }

    public function getLockedAccounts(array $filters, int $page, int $perPage): array
    {
        $where_clauses = ["u.is_deleted = 0"];
        $params = [];

        if ($filters['tab'] === 'locked') {
            $where_clauses[] = "u.temp_pass_status IN ('pending', 'done')";
        } elseif ($filters['tab'] === 'flagged') {
            $where_clauses[] = "u.failed_attempts >= 3 AND u.temp_pass_status = 'none'";
        } else {
            $where_clauses[] = "(u.temp_pass_status IN ('pending', 'done') OR u.failed_attempts >= 3)";
        }

        if ($filters['status'] === 'pending') {
            $where_clauses[] = "u.temp_pass_status = 'pending'";
        } elseif ($filters['status'] === 'done') {
            $where_clauses[] = "u.temp_pass_status = 'done'";
        }

        if (!empty($filters['search'])) {
            $where_clauses[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $s = "%{$filters['search']}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $where_sql = implode(' AND ', $where_clauses);
        $total = $this->repository->countLockedAccounts($where_sql, $params);

        $pages  = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $accounts = $this->repository->getLockedAccounts($where_sql, $params, $perPage, $offset);

        $formatted_accounts = [];
        foreach ($accounts as $acc) {
            $locked_timestamp = strtotime($acc['updated_at']);
            $acc['locked_at'] = date('d M Y', $locked_timestamp);
            $acc['lock_time'] = date('h:i A', $locked_timestamp);

            $acc['temp_pass_status'] = $acc['temp_pass_status'] ?? 'pending';

            if ($acc['temp_pass_status'] === 'done') {
                $acc['status_text'] = 'Done';
                $acc['status_badge'] = 'badge-success';
            } else {
                $acc['status_text'] = 'Pending';
                $acc['status_badge'] = 'badge-warn';
            }
            $formatted_accounts[] = $acc;
        }

        return [
            'accounts'     => $formatted_accounts,
            'total'        => $total,
            'pages'        => $pages,
            'current_page' => $page
        ];
    }

    public function getLockedAccountDetail(int $userId): array
    {
        $user = $this->repository->getLockedAccountDetail($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found', 'code' => 404];
        }

        $is_locked = ($user['failed_attempts'] >= 5) || ($user['locked_until'] && strtotime($user['locked_until']) > time());
        $lock_ts = $user['locked_until'] ? strtotime($user['locked_until']) : strtotime($user['updated_at']);

        $user['is_locked']        = $is_locked;
        $user['initials']         = strtoupper(substr($user['full_name'], 0, 1));
        $user['locked_at']        = date('d M Y', $lock_ts);
        $user['lock_time']        = date('h:i A', $lock_ts);
        $user['locked_on_date']   = date('d M Y', $lock_ts);
        $user['locked_on_ts']     = date('h:i A', $lock_ts);
        $user['locked_by']        = 'System Auto-Lock';
        $user['ip_address']       = $user['last_login_ip'] ?? 'N/A';
        $user['device']           = 'N/A';
        $user['opened_at']        = $user['opened_date'] ? date('d M Y', strtotime($user['opened_date'])) : 'N/A';
        $user['temp_password_active'] = false;
        $user['temp_password']    = null;

        if (isset($_SESSION['temp_passwords'][$userId])) {
            $temp_data = $_SESSION['temp_passwords'][$userId];
            if ($temp_data['expires'] > time()) {
                $user['temp_password_active'] = true;
                $user['temp_password'] = $temp_data['password'];
                $user['is_locked'] = true;
            } else {
                unset($_SESSION['temp_passwords'][$userId]);
            }
        }

        $logs = [];
        if ($user['customer_id']) {
            $raw_logs = $this->repository->getUserAuditLogs($user['customer_id']);
            foreach ($raw_logs as $log) {
                $outcome = 'Allow';
                $action = strtolower($log['action_type'] ?? '');
                if (strpos($action, 'lock') !== false || strpos($action, 'fail') !== false || strpos($action, 'block') !== false) {
                    $outcome = 'Block';
                }

                $logs[] = [
                    'ip'      => $log['ip_address'] ?? '::1',
                    'time'    => date('d M Y, h:i A', strtotime($log['created_at'])),
                    'outcome' => $outcome,
                    'action'  => $log['action_type'],
                    'details' => $log['details'],
                ];
            }
        }

        return ['success' => true, 'user' => $user, 'logs' => $logs];
    }

    public function getPendingApprovals(array $filters, int $page, int $perPage): array
    {
        $where_clauses = ["u.is_deleted = 0"];
        $params = [];

        if ($filters['status'] === 'pending') {
            $where_clauses[] = "u.status = 'pending'";
        } elseif ($filters['status'] === 'under_review') {
            $where_clauses[] = "u.status = 'under_review'";
        } else {
            $where_clauses[] = "u.status IN ('pending', 'under_review')";
        }

        if (!empty($filters['search'])) {
            $where_clauses[] = "(u.full_name LIKE ? OR u.customer_id LIKE ?)";
            $s = "%{$filters['search']}%";
            $params[] = $s;
            $params[] = $s;
        }

        $where_sql = implode(' AND ', $where_clauses);
        $total = $this->repository->countPendingApprovals($where_sql, $params);

        $pages  = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $applications = $this->repository->getPendingApprovals($where_sql, $params, $perPage, $offset);

        foreach ($applications as &$app) {
            $profile_pic_url = $app['profile_picture'] ? APP_URL . '/' . $app['profile_picture'] : null;
            if (!$profile_pic_url) {
                $avatar_path = 'uploads/avatars/user_' . $app['id'] . '.jpg';
                if (file_exists(FINZO_ROOT . '/' . $avatar_path)) {
                    $profile_pic_url = APP_URL . '/' . $avatar_path;
                }
            }
            $app['profile_picture_url'] = $profile_pic_url;
        }

        $stats = $this->repository->getPendingApprovalsStats();

        return [
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
        ];
    }

    public function recoverUser(int $userId, int $adminId): array
    {
        $user = $this->repository->getUserForRecovery($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found', 'code' => 404];
        }

        if (!$user['is_deleted']) {
            return ['success' => false, 'message' => 'User is not deleted', 'code' => 409];
        }

        $this->repository->recoverUser($userId, $user['customer_id']);

        global $pdo;
        if (function_exists('send_notification') && isset($pdo)) {
            send_notification(
                $pdo,
                $userId,
                'system',
                'Account Restored',
                'Your account has been restored and is now active. You can log in and continue banking.'
            );
        }

        if (function_exists('write_audit_log') && isset($pdo)) {
            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_recovered', $userId, 'user', $adminId, 'admin', "Recovered deleted user: {$user['full_name']}")
);
        }

        return ['success' => true, 'message' => 'User recovered successfully'];
    }

    public function getApplicationDetails(int $userId): array
    {
        $user = $this->repository->getApplicationUser($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Application not found', 'code' => 404];
        }

        $account = $this->repository->getAccountPreferences($user['customer_id']);

        $id_front_url = null;
        if ($user['id_front_path']) {
            $id_front_url = str_starts_with($user['id_front_path'], 'storage/')
                ? APP_URL . '/api/Admin/view-kyc-document.php?path=' . urlencode($user['id_front_path'])
                : APP_URL . '/' . $user['id_front_path'];
        }

        $id_back_url = null;
        if ($user['id_back_path']) {
            $id_back_url = str_starts_with($user['id_back_path'], 'storage/')
                ? APP_URL . '/api/Admin/view-kyc-document.php?path=' . urlencode($user['id_back_path'])
                : APP_URL . '/' . $user['id_back_path'];
        }

        $profile_pic_url = $user['profile_picture'] ? APP_URL . '/' . $user['profile_picture'] : null;
        if (!$profile_pic_url) {
            $avatar_path = 'uploads/avatars/user_' . $user['id'] . '.jpg';
            if (file_exists(FINZO_ROOT . '/' . $avatar_path)) {
                $profile_pic_url = APP_URL . '/' . $avatar_path;
            }
        }
        $user['profile_picture_url'] = $profile_pic_url;

        $logs = $this->repository->getApplicationAuditLogs($user['customer_id']);

        $first_submit_time = empty($logs) ?
            ($user['kyc_submitted_at'] ? date('d M Y, h:i A', strtotime($user['kyc_submitted_at'])) : 'Unknown') :
            date('d M Y, h:i A', strtotime($logs[0]['created_at']) - 120);

        $timeline = [
            [
                'status' => 'Account Registered',
                'time' => date('d M Y, h:i A', strtotime($user['created_at'])),
                'type' => 'completed'
            ],
            [
                'status' => 'Profile Submitted',
                'time' => $first_submit_time,
                'type' => 'completed'
            ]
        ];

        $total_logs = count($logs);
        foreach ($logs as $index => $log) {
            $reason = "Application was rejected";
            if (preg_match('/Reason:\s*(.+)$/i', $log['details'], $matches)) {
                $reason = trim($matches[1]);
            }
            $timeline[] = [
                'status' => 'Application Rejected',
                'time' => date('d M Y, h:i A', strtotime($log['created_at'])),
                'desc' => $reason,
                'type' => 'error'
            ];

            $resubmit_time = 'Unknown';
            if ($index === $total_logs - 1 && $user['kyc_submitted_at']) {
                $resubmit_time = date('d M Y, h:i A', strtotime($user['kyc_submitted_at']));
            } elseif (isset($logs[$index + 1])) {
                $resubmit_time = date('d M Y, h:i A', strtotime($logs[$index + 1]['created_at']) - 60);
            }

            $timeline[] = [
                'status' => 'Profile Resubmitted',
                'time' => $resubmit_time,
                'type' => 'completed'
            ];
        }

        if ($user['status'] === 'under_review') {
            $timeline[] = [
                'status' => 'Under Admin Review',
                'time' => 'In Progress',
                'type' => 'active'
            ];
            $timeline[] = [
                'status' => 'Account Activated',
                'time' => 'Waiting for approval',
                'type' => 'pending'
            ];
        } elseif ($user['status'] === 'pending') {
            $timeline[] = [
                'status' => 'Pending Review',
                'time' => 'Awaiting admin review',
                'type' => 'active'
            ];
            $timeline[] = [
                'status' => 'Account Activated',
                'time' => 'Waiting for approval',
                'type' => 'pending'
            ];
        } elseif ($user['status'] === 'active') {
            $timeline[] = [
                'status' => 'Approved & Activated',
                'time' => $user['kyc_submitted_at'] ? date('d M Y, h:i A', strtotime($user['kyc_submitted_at'] . ' + 1 hour')) : 'Completed',
                'type' => 'completed'
            ];
        } elseif ($user['status'] === 'rejected') {
            $timeline[] = [
                'status' => 'Application Rejected',
                'time' => 'KYC Rejected',
                'desc' => $user['rejection_reason'] ? $user['rejection_reason'] : '',
                'type' => 'error'
            ];
        }

        global $pdo;
        require_once FINZO_ROOT . '/config/functions.php';
        $expected_acc = generate_account_number($pdo);

        return [
            'success' => true,
            'user'         => $user,
            'account'      => $account,
            'id_documents' => [
                'front_url' => $id_front_url,
                'back_url'  => $id_back_url
            ],
            'timeline'     => $timeline,
            'expected_acc' => $expected_acc
        ];
    }

    public function processApplication(int $userId, string $action, string $reason, string $notes, int $adminId): array
    {
        $user = $this->repository->getApplicationUser($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found', 'code' => 404];
        }

        $adminData = $this->repository->getAdminById($adminId);
        $admin_display_id = $adminData['admin_id'] ?? $adminId;
        $customer_id = $user['customer_id'];

        global $pdo;
        require_once FINZO_ROOT . '/config/functions.php';
        require_once FINZO_ROOT . '/config/mailer.php';

        try {
            switch ($action) {
                case 'approve':
                    $pdo->beginTransaction();
                    $account_no = generate_account_number($pdo);
                    $this->repository->approveApplication($userId, $customer_id, $account_no, $admin_display_id);

                    $accountPref = $this->repository->getAccountPreferences($customer_id);
                    $initial_balance = (float)($accountPref['initial_deposit'] ?? 0);

                    if ($initial_balance > 0) {
                        $acct_id = $this->repository->getAccountIdByCustomerId($customer_id);
                        $txn_id = generate_transaction_id();
                        $this->repository->addTransaction(
                            $txn_id,
                            null,
                            $acct_id,
                            'deposit',
                            $initial_balance,
                            $initial_balance,
                            'Initial Deposit — Account Opening',
                            '',
                            'completed'
                        );
                    }
                    $pdo->commit();

                    if (function_exists('send_approved_email')) {
                        send_approved_email($user['email'], $user['full_name'], $account_no);
                    }
                    if (function_exists('send_notification')) {
                        send_notification($pdo, $userId, 'system', 'Account Approved!', "Congratulations! Your account has been approved. Your account number is {$account_no}.");
                    }
                    if (function_exists('write_audit_log')) {
                        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('kyc_approved', $userId, 'user', $adminId, 'admin', "Approved KYC for {$user['full_name']}. Account: {$account_no}")
);
                    }

                    return ['success' => true, 'message' => 'Application approved successfully', 'account_no' => $account_no];

                case 'reject':
                    if (empty($reason)) {
                        return ['success' => false, 'message' => 'Rejection reason is required', 'code' => 422];
                    }
                    $pdo->beginTransaction();
                    $this->repository->rejectApplication($userId, $customer_id, $reason, $admin_display_id);
                    $pdo->commit();

                    if (function_exists('send_rejected_email')) {
                        send_rejected_email($user['email'], $user['full_name'], $reason);
                    }
                    if (function_exists('send_notification')) {
                        send_notification($pdo, $userId, 'system', 'Application Update', "Your application has been reviewed. Status: Rejected. Reason: {$reason}. You can update your profile and resubmit.");
                    }
                    if (function_exists('write_audit_log')) {
                        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('kyc_rejected', $userId, 'user', $adminId, 'admin', "Rejected KYC for {$user['full_name']}. Reason: {$reason}")
);
                    }

                    return ['success' => true, 'message' => 'Application rejected'];

                case 'save_notes':
                    if (function_exists('write_audit_log')) {
                        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('admin_notes_saved', $userId, 'user', $adminId, 'admin', "Admin notes: {$notes}")
);
                    }
                    $this->repository->saveAdminNotes($customer_id, $notes);
                    return ['success' => true, 'message' => 'Notes saved successfully'];

                default:
                    return ['success' => false, 'message' => 'Invalid action', 'code' => 422];
            }
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function getSuspendedUsers(array $filters, int $page, int $perPage): array
    {
        $where_clauses = ["u.status = 'suspended'", "u.is_deleted = 0"];
        $params = [];

        if (!empty($filters['search'])) {
            $where_clauses[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR a.account_no LIKE ? OR u.customer_id LIKE ?)";
            $s = "%{$filters['search']}%";
            $params = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        if (!empty($filters['reason'])) {
            $where_clauses[] = "l.details LIKE ?";
            $params[] = "%Reason: %{$filters['reason']}%";
        }

        if (!empty($filters['start_date'])) {
            $where_clauses[] = "DATE(COALESCE(l.created_at, u.updated_at)) >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where_clauses[] = "DATE(COALESCE(l.created_at, u.updated_at)) <= ?";
            $params[] = $filters['end_date'];
        }

        $where_sql = implode(' AND ', $where_clauses);
        $total = $this->repository->countSuspendedUsers($where_sql, $params);

        $pages  = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $users = $this->repository->getSuspendedUsers($where_sql, $params, $perPage, $offset);

        $base_avatar_dir = FINZO_ROOT . '/uploads/avatars/';
        foreach ($users as &$u) {
            if (preg_match('/Reason:\s*(.*)$/i', $u['raw_reason'], $matches)) {
                $u['suspension_reason'] = trim($matches[1]);
            } else {
                $u['suspension_reason'] = 'Administrative Review';
            }

            if ($u['raw_suspended_at']) {
                $d = new \DateTime($u['raw_suspended_at']);
                $u['suspended_at'] = $d->format('d M Y h:i a');
            } else {
                $u['suspended_at'] = 'N/A';
            }

            $avatar_path = $base_avatar_dir . 'user_' . $u['id'] . '.jpg';
            $u['avatar_url'] = file_exists($avatar_path) ? APP_URL . '/uploads/avatars/user_' . $u['id'] . '.jpg?t=' . filemtime($avatar_path) : null;

            $u['duration'] = 'Permanent';

            unset($u['raw_reason']);
            unset($u['raw_suspended_at']);
        }
        unset($u);

        return [
            'users'        => $users,
            'total'        => $total,
            'pages'        => $pages,
            'current_page' => $page
        ];
    }

    public function getTransactions(array $filters, int $page, int $perPage, bool $isExport = false): array
    {
        $where_clauses = ["1=1"];
        $params = [];

        if ($filters['type'] !== 'all' && in_array($filters['type'], ['deposit', 'withdrawal', 'transfer', 'interest_credit'])) {
            $where_clauses[] = "t.type = ?";
            $params[] = $filters['type'];
        }

        if ($filters['status'] !== 'all' && in_array($filters['status'], ['completed', 'pending', 'failed', 'reversed'])) {
            $where_clauses[] = "t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['from_date'])) {
            $where_clauses[] = "DATE(t.created_at) >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where_clauses[] = "DATE(t.created_at) <= ?";
            $params[] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $where_clauses[] = "(t.txn_id LIKE ? OR fu.full_name LIKE ? OR tu.full_name LIKE ? OR fa.account_no LIKE ? OR ta.account_no LIKE ?)";
            $s = "%{$filters['search']}%";
            $params = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        $where_sql = implode(' AND ', $where_clauses);
        $base_sql = "
            FROM transactions t
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN users fu ON fa.user_id = fu.customer_id
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            LEFT JOIN users tu ON ta.user_id = tu.customer_id
            LEFT JOIN staff s ON t.processed_by_staff_id = s.id
            WHERE {$where_sql}
        ";

        if ($isExport) {
            return $this->repository->exportTransactions($base_sql, $params);
        }

        $total = $this->repository->countTransactions($base_sql, $params);
        $pages  = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $transactions = $this->repository->getTransactions($base_sql, $params, $perPage, $offset);
        $summary = $this->repository->getTransactionsSummary($base_sql, $params);

        return [
            'transactions' => $transactions,
            'total'        => $total,
            'pages'        => $pages,
            'current_page' => $page,
            'per_page'     => $perPage,
            'summary'      => $summary
        ];
    }

    public function getUsersList(array $filters, int $page, int $perPage): array
    {
        $where_clauses = ["u.is_deleted = 0", "u.status != 'suspended'"];
        $params = [];

        if ($filters['status'] !== 'all' && in_array($filters['status'], ['pending', 'under_review', 'active', 'rejected'])) {
            $where_clauses[] = "u.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where_clauses[] = "(u.full_name LIKE ? OR u.customer_id LIKE ?)";
            $search_param = "%{$filters['search']}%";
            $params[] = $search_param;
            $params[] = $search_param;
        }

        $where_sql = implode(' AND ', $where_clauses);
        $total = $this->repository->countUsers($where_sql, $params);

        $pages  = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $users = $this->repository->getUsers($where_sql, $params, $perPage, $offset);

        foreach ($users as &$u) {
            $profile_pic_url = $u['profile_picture'] ? APP_URL . '/' . $u['profile_picture'] : null;
            if (!$profile_pic_url) {
                $avatar_path = 'uploads/avatars/user_' . $u['id'] . '.jpg';
                if (file_exists(FINZO_ROOT . '/' . $avatar_path)) {
                    $profile_pic_url = APP_URL . '/' . $avatar_path;
                }
            }
            $u['profile_picture_url'] = $profile_pic_url;
        }
        unset($u);

        $stats = $this->repository->getUsersStats();

        return [
            'users'        => $users,
            'total'        => $total,
            'pages'        => $pages,
            'current_page' => $page,
            'per_page'     => $perPage,
            'stats'        => [
                'total_users'     => (int)($stats['total_users'] ?? 0),
                'active_users'    => (int)($stats['active_users'] ?? 0),
                'suspended_users' => (int)($stats['suspended_users'] ?? 0),
                'new_this_month'  => (int)($stats['new_this_month'] ?? 0),
                'soft_deleted'    => (int)($stats['soft_deleted'] ?? 0)
            ]
        ];
    }
    public function getSuspendedUserDetail(int $userId): array
    {
        $user = $this->repository->getSuspendedUserProfile($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Suspended user not found'];
        }

        $account = $this->repository->getSuspendedUserAccount($user['customer_id']);
        $audit = $this->repository->getUserSuspensionAudit($user['customer_id']);

        $suspension_reason = 'Administrative Review';
        $suspension_notes = 'This account was suspended for administrative review.';
        $suspended_by = 'Admin';
        $suspended_date = 'N/A';
        $suspended_time = 'N/A';

        if ($audit) {
            if (preg_match('/Reason:\s*(.*)$/i', $audit['details'], $matches)) {
                $suspension_reason = trim($matches[1]);
            }
            $suspension_notes = $audit['details'];
            $suspended_by = $audit['actor_id'];

            $d = new \DateTime($audit['created_at']);
            $suspended_date = $d->format('d M Y');
            $suspended_time = $d->format('h:i A');
        }

        $transactions = [];
        if ($account) {
            $txs = $this->repository->getRecentTransactions($account['account_no'], 10);
            foreach ($txs as $tx) {
                $is_debit = false;
                if ($tx['type'] === 'withdrawal') {
                    $is_debit = true;
                } elseif ($tx['type'] === 'transfer' && $tx['description'] !== 'Deposit') {
                    $is_debit = true;
                }

                $d = new \DateTime($tx['created_at']);
                $transactions[] = [
                    'date' => $d->format('d M Y, h:i A'),
                    'description' => $tx['description'] ?: ucfirst($tx['type']),
                    'type' => $is_debit ? 'Debit' : 'Credit',
                    'amount' => ($is_debit ? '-' : '+') . ' ₹' . number_format($tx['amount'], 2)
                ];
            }
        }

        $base_avatar_dir = UPLOAD_AVATARS_PATH;
        $avatar_path = $base_avatar_dir . 'user_' . $userId . '.jpg';
        $avatar_url = file_exists($avatar_path) ? APP_URL . '/uploads/avatars/user_' . $userId . '.jpg?t=' . filemtime($avatar_path) : null;

        $formatted_user = [
            'id' => $user['id'],
            'customer_id' => $user['customer_id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'initials' => implode('', array_map(function ($n) {
                return $n[0] ?? '';
            }, explode(' ', $user['full_name']))),
            'avatar_url' => $avatar_url,
            'account_no' => $account ? $account['account_no'] : 'N/A',
            'account_type' => $account ? ucfirst($account['account_type']) : 'N/A',
            'balance' => $account ? $account['balance'] : 0,
            'opened_at' => $account && $account['opened_at'] ? (new \DateTime($account['opened_at']))->format('d M Y') : 'N/A',
            'suspension_reason' => $suspension_reason,
            'suspension_notes' => $suspension_notes,
            'suspended_by' => $suspended_by,
            'suspended_date' => $suspended_date,
            'suspended_time' => $suspended_time,
            'admin_notes' => $suspension_notes,
            'notes_footer' => "Logged by {$suspended_by} on {$suspended_date} {$suspended_time}"
        ];

        return [
            'success' => true,
            'user' => $formatted_user,
            'transactions' => $transactions
        ];
    }

    public function unlockAccount(int $userId, int $adminId): array
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#$%!';
        $temp_pass = '';
        $temp_pass .= 'abcdefghjkmnpqrstuvwxyz'[rand(0, 23)];
        $temp_pass .= 'ABCDEFGHJKMNPQRSTUVWXYZ'[rand(0, 23)];
        $temp_pass .= '23456789'[rand(0, 7)];
        $temp_pass .= '@#$%!'[rand(0, 4)];
        for ($i = 4; $i < 12; $i++) {
            $temp_pass .= $chars[rand(0, strlen($chars) - 1)];
        }
        $temp_pass = str_shuffle($temp_pass);

        $user = $this->repository->getUserForUnlock($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found', 'code' => 404];
        }

        $password_hash = password_hash($temp_pass, PASSWORD_BCRYPT);
        $this->repository->unlockUserAccount($password_hash, $userId);

        global $pdo;
        if (function_exists('send_notification') && isset($pdo)) {
            send_notification(
                $pdo,
                $userId,
                'security',
                'Account Unlocked',
                'Your account has been unlocked by an administrator. Your password has been reset to a temporary one. Please check with an admin if you did not request this.'
            );
        }

        if (function_exists('write_audit_log') && isset($pdo)) {
            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('account_unlocked', $userId, 'user', $adminId, 'admin', "Manually unlocked account and reset password for {$user['full_name']} ({$user['email']}). Previous attempts: {$user['failed_attempts']}")
);
        }

        if (!isset($_SESSION['temp_passwords'])) {
            $_SESSION['temp_passwords'] = [];
        }
        $_SESSION['temp_passwords'][$userId] = [
            'password' => $temp_pass,
            'expires'  => time() + 300
        ];

        return [
            'success' => true,
            'message' => 'Account unlocked and temporary password generated.',
            'temp_password' => $temp_pass
        ];
    }

    public function processUserAction(int $userId, string $action, string $reason, string $password, string $balanceOption, int $adminId): array
    {
        $valid_actions = ['suspend', 'reinstate', 'delete', 'recover', 'hard_delete', 'delete_account_only'];
        if (!in_array($action, $valid_actions)) {
            return ['success' => false, 'message' => 'Invalid action', 'code' => 422];
        }

        $user = $this->repository->getUserForAction($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found', 'code' => 404];
        }

        if (in_array($action, ['suspend', 'delete'])) {
            $admin = $this->repository->getAdminPassword($adminId);
            if (!$admin || !password_verify($password, $admin['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid administrative credentials. Access denied.', 'code' => 401];
            }
        }

        global $pdo;
        switch ($action) {
            case 'suspend':
                if (function_exists('check_admin_permission') && isset($pdo)) {
                    $this->security->checkAdminPermission($pdo, $adminId, 'suspend_user');
                }
                if ($user['status'] === 'suspended') {
                    return ['success' => false, 'message' => 'User is already suspended', 'code' => 409];
                }

                $this->repository->suspendUserAccount($userId, $user['customer_id']);

                if (function_exists('send_notification') && isset($pdo)) {
                    send_notification(
                        $pdo,
                        $userId,
                        'security',
                        'Account Suspended',
                        'Your account has been suspended. Reason: ' . ($reason ?: 'No reason provided') . '. Contact support for assistance.'
                    );
                }
                if (function_exists('write_audit_log') && isset($pdo)) {
                    \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_suspended', $userId, 'user', $adminId, 'admin', "User {$user['full_name']} suspended. Reason: {$reason}")
);
                }
                return ['success' => true, 'message' => 'User suspended successfully'];

            case 'reinstate':
                if (function_exists('check_admin_permission') && isset($pdo)) {
                    $this->security->checkAdminPermission($pdo, $adminId, 'reinstate_user');
                }
                if ($user['status'] === 'active') {
                    return ['success' => true, 'message' => 'User is already active'];
                }
                if ($user['status'] !== 'suspended') {
                    return ['success' => false, 'message' => 'User is not suspended', 'code' => 409];
                }

                $this->repository->reinstateUserAccount($userId, $user['customer_id']);

                if (function_exists('send_notification') && isset($pdo)) {
                    send_notification($pdo, $userId, 'security', 'Account Reinstated', 'Your account has been reinstated and is now active. You can log in and transact normally.');
                }
                if (function_exists('write_audit_log') && isset($pdo)) {
                    \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_reinstated', $userId, 'user', $adminId, 'admin', "User {$user['full_name']} reinstated")
);
                }
                return ['success' => true, 'message' => 'User reinstated successfully'];

            case 'delete':
                if (function_exists('check_admin_permission') && isset($pdo)) {
                    $this->security->checkAdminPermission($pdo, $adminId, 'soft_delete_user');
                }
                if ($user['is_deleted']) {
                    return ['success' => true, 'message' => 'User is already deleted'];
                }

                $this->repository->softDeleteUserAccount($userId, $user['customer_id']);

                if (function_exists('write_audit_log') && isset($pdo)) {
                    \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_deleted', $userId, 'user', $adminId, 'admin', "User {$user['full_name']} soft-deleted. Reason: {$reason}")
);
                }
                return ['success' => true, 'message' => 'User deleted successfully'];

            case 'recover':
                if (function_exists('check_admin_permission') && isset($pdo)) {
                    $this->security->checkAdminPermission($pdo, $adminId, 'soft_delete_user');
                }
                if (!$user['is_deleted']) {
                    return ['success' => false, 'message' => 'User is not deleted', 'code' => 409];
                }

                $this->repository->restoreUserFromSoftDelete($userId);

                if ($balanceOption === 'mandatory') {
                    $account = $this->repository->getUserForActionByCustomer($user['customer_id']);
                    if ($account && $account['balance'] > 500) {
                        $deduction_amount = $account['balance'] - 500;
                        $txn_id = 'TXN' . strtoupper(uniqid());
                        $this->repository->addSystemChargeTransaction($txn_id, $account['id'], $deduction_amount);
                        if (function_exists('send_notification') && isset($pdo)) {
                            send_notification($pdo, $userId, 'system', 'Account Restored – Balance Adjusted', 'Your account has been restored. As per the recovery policy, your balance has been adjusted to the mandatory minimum of ₹500.00. An amount of ₹' . number_format($deduction_amount, 2) . ' was cleared as part of the restore process. This adjustment does not count toward your daily transfer limit.');
                        }
                    } else {
                        if (function_exists('send_notification') && isset($pdo)) {
                            send_notification($pdo, $userId, 'system', 'Account Restored', 'Your account has been restored and is now active with your mandatory minimum balance. You can log in and continue banking.');
                        }
                    }
                    $this->repository->updateAccountBalanceLease500($user['customer_id']);
                } else {
                    $this->repository->activateAccount($user['customer_id']);
                    if (function_exists('send_notification') && isset($pdo)) {
                        send_notification($pdo, $userId, 'system', 'Account Restored', 'Your account has been restored and is now active. Your full balance has been retained. You can log in and continue banking.');
                    }
                }

                if (function_exists('write_audit_log') && isset($pdo)) {
                    \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_recovered', $userId, 'user', $adminId, 'admin', "User {$user['full_name']} recovered from soft-delete state" . ($balanceOption === 'mandatory' ? ' with mandatory balance of 500' : ''))
);
                }
                return ['success' => true, 'message' => 'User recovered successfully'];

            case 'hard_delete':
                if (function_exists('check_admin_permission') && isset($pdo)) {
                    $this->security->checkAdminPermission($pdo, $adminId, 'soft_delete_user');
                }
                $this->repository->hardDeleteUserAccount($userId, $user['customer_id']);
                if (function_exists('write_audit_log') && isset($pdo)) {
                    \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_hard_deleted', $userId, 'user', $adminId, 'admin', "User {$user['full_name']} permanently deleted.")
);
                }
                return ['success' => true, 'message' => 'User permanently deleted'];

            case 'delete_account_only':
                if (function_exists('check_admin_permission') && isset($pdo)) {
                    $this->security->checkAdminPermission($pdo, $adminId, 'soft_delete_user');
                }
                $this->repository->deleteAccountOnly($user['customer_id']);
                if (function_exists('write_audit_log') && isset($pdo)) {
                    \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('account_hard_deleted', $userId, 'user', $adminId, 'admin', "Account for user {$user['full_name']} permanently deleted.")
);
                }
                return ['success' => true, 'message' => 'Account permanently deleted'];
        }
        return ['success' => false, 'message' => 'Action failed', 'code' => 500];
    }

    public function getUserDetail(int $userId): array
    {
        $user = $this->repository->getUserProfile($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $accounts = $this->repository->getUserAccounts($user['customer_id']);
        $transactions = [];
        if (count($accounts) > 0) {
            $account_ids = array_column($accounts, 'id');
            $transactions = $this->repository->getAccountsRecentTransactions($account_ids, 20);
        }

        $id_front_url = $user['id_front_path'] ? APP_URL . '/' . $user['id_front_path'] : null;
        $id_back_url  = $user['id_back_path'] ? APP_URL . '/' . $user['id_back_path'] : null;
        $avatar_url = !empty($user['profile_picture']) ? APP_URL . '/' . $user['profile_picture'] : null;

        return [
            'success' => true,
            'user' => $user,
            'accounts' => $accounts,
            'transactions' => $transactions,
            'avatar_url' => $avatar_url,
            'id_documents' => [
                'front_url' => $id_front_url,
                'back_url'  => $id_back_url
            ]
        ];
    }
}
