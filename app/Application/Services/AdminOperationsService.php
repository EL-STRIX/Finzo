<?php

namespace App\Application\Services;

use App\Shared\Security\SecurityService;

use App\Infrastructure\Repositories\AdminOperationsRepository;

class AdminOperationsService implements \App\Domain\Contracts\Services\AdminOperationsServiceInterface
{
    protected ?\App\Domain\Contracts\Security\SecurityServiceInterface $security;

    protected AdminOperationsRepository $repository;
    /**
     *   construct.
     *
     * @param \App\Domain\Contracts\Repositories\AdminOperationsRepositoryInterface $repository
     * @param ?\App\Domain\Contracts\Security\SecurityServiceInterface $security
     */
    public function __construct(\App\Domain\Contracts\Repositories\AdminOperationsRepositoryInterface $repository, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security;
        $this->repository = $repository;
    }
    /**
     * Get Audit Log.
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getAuditLog(array $filters, int $page, int $perPage): array
    {
        $total = $this->repository->countAuditLogs($filters);

        $pages  = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $entries = $this->repository->getAuditLogs($filters, $perPage, $offset);
        $action_types = $this->repository->getAuditLogActionTypes();
        $stats = $this->repository->getAuditLogStats($filters);

        return [
            'entries'      => $entries,
            'total'        => $total,
            'pages'        => $pages,
            'current_page' => $page,
            'per_page'     => $perPage,
            'action_types' => $action_types,
            'stats'        => [
                'total'    => $total,
                'admin'    => (int)($stats['admin_count'] ?? 0),
                'staff'    => (int)($stats['staff_count'] ?? 0),
                'user'     => (int)($stats['user_count'] ?? 0),
                'system'   => (int)($stats['system_count'] ?? 0),
                'success'  => (int)($stats['success_count'] ?? 0),
                'failure'  => (int)($stats['failure_count'] ?? 0)
            ]
        ];
    }

    /**
     * Export Audit Logs as CSV generator.
     *
     * @param array $filters
     * @return \Generator
     */
    public function exportAuditLogs(array $filters): \Generator
    {
        return $this->repository->exportAuditLogsGenerator($filters);
    }
    /**
     * Get Dashboard Data.
     *
     * @param int $adminId
     * @return array
     */
    public function getDashboardData(int $adminId): array
    {
        $stats = $this->repository->getDashboardStats();

        $stats_cache = \App\Shared\Core\Cache\CacheManager::getInstance()->get('admin_dashboard_stats', function() {
            return $this->repository->getCachedDashboardStats();
        }, 15); // 15 seconds cache

        $stats = array_merge($stats, $stats_cache);

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

        $monthly_chart_data = \App\Shared\Core\Cache\CacheManager::getInstance()->get('admin_dashboard_chart', function() {
            $data = [];
            for ($i = 5; $i >= 0; $i--) {
                $month_date = date('Y-m', strtotime("-{$i} months"));
                $month_label = date('M Y', strtotime("-{$i} months"));
                $month_data = $this->repository->getMonthlyChartDataRow($month_date);

                $data[] = [
                    'month'       => $month_label,
                    'deposits'    => (float)($month_data['deposits'] ?? 0),
                    'withdrawals' => (float)($month_data['withdrawals'] ?? 0),
                    'transfers'   => (float)($month_data['transfers'] ?? 0),
                    'count'       => (int)($month_data['count'] ?? 0)
                ];
            }
            return $data;
        }, 30); // 30 seconds cache

        $admin_data = $this->repository->getAdminById($adminId);

        return [
            'stats' => $stats,
            'recent_users' => $recent_users,
            'monthly_chart_data' => $monthly_chart_data,
            'admin' => $admin_data
        ];
    }
    /**
     * Get Deleted Users.
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
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

        foreach ($users as &$u) {
            $avatar_url = null;
            if (!empty($u['profile_picture'])) {
                $avatar_url = str_starts_with($u['profile_picture'], 'http')
                    ? $u['profile_picture']
                    : APP_URL . '/' . ltrim($u['profile_picture'], '/');
            } else {
                $avatar_path = FINZO_ROOT . '/uploads/avatars/user_' . $u['id'] . '.jpg';
                if (file_exists($avatar_path)) {
                    $avatar_url = APP_URL . '/uploads/avatars/user_' . $u['id'] . '.jpg?t=' . filemtime($avatar_path);
                }
            }
            $u['avatar_url'] = $avatar_url;
        }
        unset($u);

        return [
            'users' => $users,
            'total' => $total,
            'pages' => $pages,
            'current_page' => $page
        ];
    }
    /**
     * Get Interest Data.
     *
     * @return array
     */
    public function getInterestData(): array
    {
        $current_rate_row = $this->repository->getCurrentInterestRate();
        $current_rate = $current_rate_row ? (float)$current_rate_row['rate'] : 4.25;
        $last_change_date = $current_rate_row ? date('d M Y', strtotime($current_rate_row['created_at'])) : '-';

        $total_interest = $this->repository->getTotalInterestCredited();
        $stats = [
            'total_interest_credited' => '₹' . number_format($total_interest, 2),
            'earning_accounts' => $this->repository->getEarningAccountsCount(),
            'interest_delta' => '+0%'
        ];

        $this_month = $this->repository->getThisMonthInterest();
        $last_month = $this->repository->getLastMonthInterest();

        if ($last_month > 0) {
            $delta = (($this_month - $last_month) / $last_month) * 100;
            $stats['interest_delta'] = ($delta > 0 ? '+' : '') . round($delta, 1) . '%';
        }

        $chart_data = \App\Shared\Core\Cache\CacheManager::getInstance()->get('admin_interest_chart', function() {
            $data = [];
            for ($i = 11; $i >= 0; $i--) {
                $data[] = $this->repository->getHistoricalMonthInterest($i);
            }
            return $data;
        }, 86400); // 24 hours cache

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
    /**
     * Update Interest Rate.
     *
     * @param float $new_rate
     * @param string $apply_from
     * @param string $custom_date
     * @param int $adminId
     * @return array
     */
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
            $pdo = \App\Shared\Core\Database::getInstance();

            foreach ($savings_accounts as $acc) {
                try {
                    $this->repository->beginTransaction();
                    $lockedAcc = $this->repository->getAccountForUpdate($acc['id']);
                    if (!$lockedAcc) {
                        $this->repository->rollBack();
                        continue;
                    }

                    $balance = (float)$lockedAcc['balance'];
                    $interest_amount = round($balance * ($new_rate / 100) / 12, 2);

                    if ($interest_amount >= 0.01) {
                        $new_balance = $balance + $interest_amount;
                        $transactionId = 'TXN' . strtoupper(bin2hex(random_bytes(8)));

                        $this->repository->addTransaction(
                            $transactionId,
                            null,
                            $lockedAcc['id'],
                            'interest_credit',
                            $interest_amount,
                            $new_balance,
                            "Savings Interest Credit",
                            "Interest credited at " . number_format($new_rate, 2) . "% p.a.",
                            'completed'
                        );

                        $this->repository->updateAccountBalance($new_balance, $lockedAcc['id']);

                        $notifMessage = "₹" . number_format($interest_amount, 2) . " interest has been credited to your savings account. New balance: ₹" . number_format($new_balance, 2);
                        $this->repository->addNotification($lockedAcc['customer_id'] ?? $acc['user_id'], 'transaction', 'Interest Credited', $notifMessage);
                    }
                    $this->repository->commit();
                } catch (\Throwable $e) {
                    if ($this->repository->inTransaction()) {
                        $this->repository->rollBack();
                    }
                    \App\Shared\Core\Logging\Logger::getInstance()->error('Interest credit error for account ' . $acc['id'], ['context' => $e->getMessage()]);
                }
            }
        }

        // Global function from config/functions.php
        
            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('interest_rate_updated', null, \App\Domain\Enums\RoleEnum::SYSTEM->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "Interest rate changed to {$new_rate}%. Apply from: {$apply_from}" . ($apply_date ? " ({$apply_date})" : ''))
);
        

        return ['success' => true, 'message' => "Interest rate updated to {$new_rate}%"];
    }
    /**
     * Export Users.
     *
     * @param array $filters
     * @return array|\Generator
     */
    public function exportUsers(array $filters): array|\Generator
    {
        $where_clauses = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where_clauses[] = "(u.full_name LIKE ? OR u.customer_id LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $search_param = "%{$filters['search']}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
        }

        $orderSql = "u.created_at DESC";
        $this->applyUserFiltersAndSort($filters, $where_clauses, $orderSql);

        if (empty($where_clauses)) {
            $where_clauses[] = "1=1";
        }

        $where_sql = implode(' AND ', $where_clauses);

        // Decrypt the PII ID Number for cleartext export (consistent with the
        // administrative user detail services). Legacy plaintext rows are
        // returned unchanged by EncryptionService::decrypt().
        $crypto = new \App\Shared\Security\EncryptionService();
        $rows = $this->repository->exportUsersGenerator($where_sql, $params, $orderSql);

        foreach ($rows as $row) {
            if (isset($row['ID Number']) && $row['ID Number'] !== '') {
                try {
                    $row['ID Number'] = $crypto->decrypt($row['ID Number']);
                } catch (\Throwable $e) {
                    // Ignore decryption failures and export the stored value.
                }
            }
            yield $row;
        }
    }
    /**
     * Get Locked Accounts.
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getLockedAccounts(array $filters, int $page, int $perPage): array
    {
        $where_clauses = ["u.is_deleted = 0"];
        $params = [];

        // An account is locked if locked_until > NOW() OR failed_attempts >= 5 OR temp_pass_status = 'pending'
        if ($filters['tab'] === 'locked') {
            $where_clauses[] = "(u.locked_until > NOW() OR u.failed_attempts >= 5 OR u.temp_pass_status = 'pending')";
        } elseif ($filters['tab'] === 'flagged') {
            $where_clauses[] = "(u.failed_attempts >= 3 AND (u.locked_until IS NULL OR u.locked_until <= NOW()) AND u.temp_pass_status = 'none')";
        } else {
            $where_clauses[] = "(u.locked_until > NOW() OR u.failed_attempts >= 5 OR u.temp_pass_status = 'pending')";
        }

        if ($filters['status'] === \App\Domain\Enums\AccountStatusEnum::PENDING->value) {
            $where_clauses[] = "(u.temp_pass_status = 'none' AND (u.locked_until > NOW() OR u.failed_attempts >= 5))";
        } elseif ($filters['status'] === 'done') {
            $where_clauses[] = "u.temp_pass_status = 'pending'";
        }

        if (!empty($filters['type'])) {
            $where_clauses[] = "a.account_type = ?";
            $params[] = strtolower($filters['type']);
        }

        if (!empty($filters['search'])) {
            $where_clauses[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR a.account_no LIKE ?)";
            $s = "%{$filters['search']}%";
            $params[] = $s;
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
            $locked_timestamp = ($acc['locked_until'] && strtotime($acc['locked_until']) > 0) ? strtotime($acc['locked_until']) : strtotime($acc['updated_at']);
            $acc['locked_at'] = date('d M Y', $locked_timestamp);
            $acc['lock_time'] = date('h:i A', $locked_timestamp);

            $avatar_url = null;
            if (!empty($acc['profile_picture'])) {
                $avatar_url = str_starts_with($acc['profile_picture'], 'http')
                    ? $acc['profile_picture']
                    : APP_URL . '/' . ltrim($acc['profile_picture'], '/');
            } else {
                $avatar_path = FINZO_ROOT . '/uploads/avatars/user_' . $acc['id'] . '.jpg';
                if (file_exists($avatar_path)) {
                    $avatar_url = APP_URL . '/uploads/avatars/user_' . $acc['id'] . '.jpg?t=' . filemtime($avatar_path);
                }
            }
            $acc['avatar_url'] = $avatar_url;

            $is_done = ($acc['temp_pass_status'] === 'pending');
            $acc['status_text'] = $is_done ? 'Done' : 'Pending';
            $acc['status_badge'] = $is_done ? 'badge-success' : 'badge-warn';
            $formatted_accounts[] = $acc;
        }

        return [
            'accounts'     => $formatted_accounts,
            'total'        => $total,
            'pages'        => $pages,
            'current_page' => $page
        ];
    }
    /**
     * Get Locked Account Detail.
     *
     * @param int $userId
     * @return array
     */
    public function getLockedAccountDetail(int $userId): array
    {
        $user = $this->repository->getLockedAccountDetail($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found', 'code' => 404];
        }

        $is_locked = ($user['failed_attempts'] >= 5) || ($user['locked_until'] && strtotime($user['locked_until']) > time());
        $lock_ts = $user['locked_until'] ? strtotime($user['locked_until']) : strtotime($user['updated_at']);

        $avatar_url = null;
        if (!empty($user['profile_picture'])) {
            $avatar_url = str_starts_with($user['profile_picture'], 'http')
                ? $user['profile_picture']
                : APP_URL . '/' . ltrim($user['profile_picture'], '/');
        } else {
            $avatar_path = FINZO_ROOT . '/uploads/avatars/user_' . $userId . '.jpg';
            if (file_exists($avatar_path)) {
                $avatar_url = APP_URL . '/uploads/avatars/user_' . $userId . '.jpg?t=' . filemtime($avatar_path);
            }
        }
        $user['avatar_url'] = $avatar_url;

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
            foreach ($raw_logs ?: [] as $log) {
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
    /**
     * Get Pending Approvals.
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getPendingApprovals(array $filters, int $page, int $perPage): array
    {
        $where_clauses = ["u.is_deleted = 0"];
        $params = [];

        if ($filters['status'] === \App\Domain\Enums\AccountStatusEnum::PENDING->value) {
            $where_clauses[] = "u.status = 'pending' AND u.kyc_submitted_at IS NOT NULL";
        } elseif ($filters['status'] === 'under_review') {
            $where_clauses[] = "u.status = 'under_review'";
        } elseif ($filters['status'] === 'rejected') {
            $where_clauses[] = "u.status = 'rejected'";
        } else {
            // Default / All Pending: show only under_review, rejected, and pending with submitted KYC
            $where_clauses[] = "(u.status = 'under_review' OR u.status = 'rejected' OR (u.status = 'pending' AND u.kyc_submitted_at IS NOT NULL))";
        }

        // Search Filter (Full name, Customer ID, Email, Phone)
        if (!empty($filters['search'])) {
            $where_clauses[] = "(u.full_name LIKE ? OR u.customer_id LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $s = "%{$filters['search']}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        // Account Type Filter
        if (!empty($filters['account_type']) && $filters['account_type'] !== 'all') {
            $where_clauses[] = "LOWER(COALESCE(a.account_type, k.account_type, 'savings')) = ?";
            $params[] = strtolower($filters['account_type']);
        }

        // ID Type Filter
        if (!empty($filters['id_type']) && $filters['id_type'] !== 'all') {
            $where_clauses[] = "LOWER(COALESCE(k.id_type, u.id_type, '')) = ?";
            $params[] = strtolower($filters['id_type']);
        }

        $where_sql = implode(' AND ', $where_clauses);
        $total = $this->repository->countPendingApprovals($where_sql, $params);

        $pages  = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $sort = $filters['sort'] ?? 'newest';
        $orderBy = match ($sort) {
            'oldest'    => 'u.kyc_submitted_at ASC, u.created_at ASC',
            'name_asc'  => 'u.full_name ASC',
            'name_desc' => 'u.full_name DESC',
            default     => 'u.kyc_submitted_at DESC, u.created_at DESC'
        };

        $applications = $this->repository->getPendingApprovals($where_sql, $params, $perPage, $offset, $orderBy);

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
    /**
     * Recover User.
     *
     * @param int $userId
* @param int $adminId
     * @return array
     */
    public function recoverUser(int $userId, int $adminId): array
    {
        $pdo = \App\Shared\Core\Database::getInstance();
        $user = $this->repository->getUserForRecovery($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found', 'code' => 404];
        }

        if (!$user['is_deleted']) {
            return ['success' => false, 'message' => 'User is not deleted', 'code' => 409];
        }

        $this->repository->recoverUser($userId, $user['customer_id']);

        if (function_exists('send_notification') && isset($pdo)) {
            send_notification($pdo, $userId, \App\Domain\Enums\NotificationTypeEnum::SYSTEM->value,
                'Account Restored',
                'Your account has been restored and is now active. You can log in and continue banking.'
            );
        }

        
            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_recovered', $userId, \App\Domain\Enums\RoleEnum::USER->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "Recovered deleted user: {$user['full_name']}")
);
        

        return ['success' => true, 'message' => 'User recovered successfully'];
    }
    /**
     * Get Application Details.
     *
     * @param int $userId
     * @return array
     */
    public function getApplicationDetails(int $userId): array
    {
        $pdo = \App\Shared\Core\Database::getInstance();
        $user = $this->repository->getApplicationUser($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Application not found', 'code' => 404];
        }

        if (!empty($user['id_number'])) {
            try {
                $crypto = new \App\Shared\Security\EncryptionService();
                $user['id_number'] = $crypto->decrypt($user['id_number']);
            } catch (\Throwable $e) {
                // Ignore decryption errors (e.g. if plain text)
            }
        }

        $account = $this->repository->getAccountPreferences($user['customer_id']);

        $id_front_url = null;
        if (!empty($user['id_front_path'])) {
            $id_front_url = str_starts_with($user['id_front_path'], 'storage/')
                ? APP_URL . '/api/v1/users/' . $user['id'] . '/kyc?path=' . urlencode($user['id_front_path'])
                : (str_starts_with($user['id_front_path'], 'http') ? $user['id_front_path'] : APP_URL . '/' . ltrim($user['id_front_path'], '/'));
        }

        $id_back_url = null;
        if (!empty($user['id_back_path'])) {
            $id_back_url = str_starts_with($user['id_back_path'], 'storage/')
                ? APP_URL . '/api/v1/users/' . $user['id'] . '/kyc?path=' . urlencode($user['id_back_path'])
                : (str_starts_with($user['id_back_path'], 'http') ? $user['id_back_path'] : APP_URL . '/' . ltrim($user['id_back_path'], '/'));
        }

        $profile_pic_url = !empty($user['profile_picture'])
            ? (str_starts_with($user['profile_picture'], 'http') ? $user['profile_picture'] : APP_URL . '/' . ltrim($user['profile_picture'], '/'))
            : null;
        if (!$profile_pic_url) {
            $avatar_path = 'public/uploads/avatars/user_' . $user['id'] . '.jpg';
            if (file_exists(FINZO_ROOT . '/' . $avatar_path)) {
                $profile_pic_url = APP_URL . '/uploads/avatars/user_' . $user['id'] . '.jpg';
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
                'type' => \App\Domain\Enums\AccountStatusEnum::COMPLETED->value
            ],
            [
                'status' => 'Profile Submitted',
                'time' => $first_submit_time,
                'type' => \App\Domain\Enums\AccountStatusEnum::COMPLETED->value
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
                'type' => \App\Domain\Enums\AccountStatusEnum::COMPLETED->value
            ];
        }

        if ($user['status'] === 'under_review') {
            $timeline[] = [
                'status' => 'Under Admin Review',
                'time' => 'In Progress',
                'type' => \App\Domain\Enums\AccountStatusEnum::ACTIVE->value
            ];
            $timeline[] = [
                'status' => 'Account Activated',
                'time' => 'Waiting for approval',
                'type' => \App\Domain\Enums\AccountStatusEnum::PENDING->value
            ];
        } elseif ($user['status'] === \App\Domain\Enums\AccountStatusEnum::PENDING->value) {
            $timeline[] = [
                'status' => 'Pending Review',
                'time' => 'Awaiting admin review',
                'type' => \App\Domain\Enums\AccountStatusEnum::ACTIVE->value
            ];
            $timeline[] = [
                'status' => 'Account Activated',
                'time' => 'Waiting for approval',
                'type' => \App\Domain\Enums\AccountStatusEnum::PENDING->value
            ];
        } elseif ($user['status'] === \App\Domain\Enums\AccountStatusEnum::ACTIVE->value) {
            // Prefer the real approval timestamp recorded on the KYC application;
            // fall back to the legacy heuristic for rows approved before this column was used.
            $approval_time = !empty($user['approval_reviewed_at'])
                ? date('d M Y, h:i A', strtotime($user['approval_reviewed_at']))
                : ($user['kyc_submitted_at'] ? date('d M Y, h:i A', strtotime($user['kyc_submitted_at'] . ' + 1 hour')) : 'Completed');
            $timeline[] = [
                'status' => 'Approved & Activated',
                'time' => $approval_time,
                'type' => \App\Domain\Enums\AccountStatusEnum::COMPLETED->value
            ];
        } elseif ($user['status'] === \App\Domain\Enums\AccountStatusEnum::REJECTED->value) {
            $timeline[] = [
                'status' => 'Application Rejected',
                'time' => 'KYC Rejected',
                'desc' => $user['rejection_reason'] ? $user['rejection_reason'] : '',
                'type' => 'error'
            ];
        }

        require_once FINZO_ROOT . '/config/functions.php';
        $expected_acc = generate_account_number($pdo);

        return [
            'success' => true,
            'user'         => $user,
            'account'      => $account,
            'admin_notes'  => $user['admin_notes'] ?? null,
            'id_documents' => [
                'front_url' => $id_front_url,
                'back_url'  => $id_back_url
            ],
            'timeline'     => $timeline,
            'expected_acc' => $expected_acc
        ];
    }
    /**
     * Process Application.
     *
     * @param int $userId
     * @param string $action
     * @param string $reason
     * @param string $notes
     * @param int $adminId
     * @return array
     */
    public function processApplication(int $userId, string $action, string $reason, string $notes, int $adminId): array
    {
        $pdo = \App\Shared\Core\Database::getInstance();
        $user = $this->repository->getApplicationUser($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found', 'code' => 404];
        }

        $adminData = $this->repository->getAdminById($adminId);
        $admin_display_id = $adminData['admin_id'] ?? $adminId;
        $customer_id = $user['customer_id'];

        // Server-side state guard: KYC decisions are only valid for applications
        // that are still awaiting review. Prevents re-approving an already approved
        // account (which would regenerate its account number and duplicate the
        // initial-deposit ledger entry) and prevents rejecting an already active
        // customer via direct API calls.
        if (in_array($action, ['approve', 'reject'], true)
            && !in_array($user['status'], ['pending', 'under_review'], true)) {
            return [
                'success' => false,
                'message' => "This application has already been processed (current status: {$user['status']}).",
                'code'    => 409
            ];
        }

        require_once FINZO_ROOT . '/config/functions.php';
        require_once FINZO_ROOT . '/config/mailer.php';

        try {
            switch ($action) {
                case 'approve':
                    $this->repository->beginTransaction();
                    $accountNumber = generate_account_number($pdo);
                    $this->repository->approveApplication($userId, $customer_id, $accountNumber, $admin_display_id);

                    $accountPref = $this->repository->getAccountPreferences($customer_id);
                    $initial_balance = (float)($accountPref['initial_deposit'] ?? 0);

                    if ($initial_balance > 0) {
                        $acct_id = $this->repository->getAccountIdByCustomerId($customer_id);
                        $transactionId = generate_transaction_id();
                        $this->repository->addTransaction(
                            $transactionId,
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
                    $this->repository->commit();

                    if (function_exists('send_approved_email')) {
                        send_approved_email($user['email'], $user['full_name'], $accountNumber);
                    }
                    if (function_exists('send_notification')) {
                        send_notification($pdo, $userId, \App\Domain\Enums\NotificationTypeEnum::SYSTEM->value, 'Account Approved!', "Congratulations! Your account has been approved. Your account number is {$accountNumber}.");
                    }
                    
                        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction(\App\Domain\Enums\AuditActionEnum::KYC_APPROVED->value, $userId, \App\Domain\Enums\RoleEnum::USER->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "Approved KYC for {$user['full_name']}. Account: {$accountNumber}")
);
                    

                    return ['success' => true, 'message' => 'Application approved successfully', 'account_no' => $accountNumber];

                case 'reject':
                    if (empty($reason)) {
                        return ['success' => false, 'message' => 'Rejection reason is required', 'code' => 422];
                    }
                    $this->repository->beginTransaction();
                    $this->repository->rejectApplication($userId, $customer_id, $reason, $admin_display_id);
                    $this->repository->commit();

                    if (function_exists('send_rejected_email')) {
                        send_rejected_email($user['email'], $user['full_name'], $reason);
                    }
                    if (function_exists('send_notification')) {
                        send_notification($pdo, $userId, \App\Domain\Enums\NotificationTypeEnum::SYSTEM->value, 'Application Update', "Your application has been reviewed. Status: Rejected. Reason: {$reason}. You can update your profile and resubmit.");
                    }
                    
                        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction(\App\Domain\Enums\AuditActionEnum::KYC_REJECTED->value, $userId, \App\Domain\Enums\RoleEnum::USER->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "Rejected KYC for {$user['full_name']}. Reason: {$reason}")
);
                    

                    return ['success' => true, 'message' => 'Application rejected'];

                case 'save_notes':
                    
                        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('admin_notes_saved', $userId, \App\Domain\Enums\RoleEnum::USER->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "Admin notes: {$notes}")
);
                    
                    $this->repository->saveAdminNotes($customer_id, $notes);
                    return ['success' => true, 'message' => 'Notes saved successfully'];

                default:
                    return ['success' => false, 'message' => 'Invalid action', 'code' => 422];
            }
        } catch (\Exception $e) {
            if ($this->repository->inTransaction()) {
                $this->repository->rollBack();
            }
            throw $e;
        }
    }
    /**
     * Get Suspended Users.
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
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

            $avatar_url = null;
            if (!empty($u['profile_picture'])) {
                $avatar_url = str_starts_with($u['profile_picture'], 'http')
                    ? $u['profile_picture']
                    : APP_URL . '/' . ltrim($u['profile_picture'], '/');
            } else {
                $avatar_path = $base_avatar_dir . 'user_' . $u['id'] . '.jpg';
                if (file_exists($avatar_path)) {
                    $avatar_url = APP_URL . '/uploads/avatars/user_' . $u['id'] . '.jpg?t=' . filemtime($avatar_path);
                }
            }
            $u['avatar_url'] = $avatar_url;
            $u['profile_picture_url'] = $avatar_url;

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
    /**
     * Get Transactions.
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @param bool $isExport
     * @return array
     */
    public function getTransactions(array $filters, int $page, int $perPage, bool $isExport = false): array|\Generator
    {
        $where_clauses = ["1=1"];
        $params = [];

        if (isset($filters['type']) && $filters['type'] !== 'all' && in_array($filters['type'], ['deposit', 'withdrawal', 'transfer', 'interest_credit'])) {
            $where_clauses[] = "t.type = ?";
            $params[] = $filters['type'];
        }

        if (isset($filters['status']) && $filters['status'] !== 'all' && in_array($filters['status'], ['completed', 'pending', 'failed', 'reversed'])) {
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

        if (!empty($filters['min_amount']) && is_numeric($filters['min_amount'])) {
            $where_clauses[] = "t.amount >= ?";
            $params[] = (float)$filters['min_amount'];
        }

        if (!empty($filters['max_amount']) && is_numeric($filters['max_amount'])) {
            $where_clauses[] = "t.amount <= ?";
            $params[] = (float)$filters['max_amount'];
        }

        if (!empty($filters['search'])) {
            $where_clauses[] = "(t.txn_id LIKE ? OR fu.full_name LIKE ? OR tu.full_name LIKE ? OR fa.account_no LIKE ? OR ta.account_no LIKE ? OR s.staff_id LIKE ? OR t.description LIKE ?)";
            $s = "%{$filters['search']}%";
            $params = array_merge($params, [$s, $s, $s, $s, $s, $s, $s]);
        }

        $sortMap = [
            'newest'      => 't.created_at DESC, t.id DESC',
            'oldest'      => 't.created_at ASC, t.id ASC',
            'amount_high' => 't.amount DESC, t.created_at DESC',
            'amount_low'  => 't.amount ASC, t.created_at DESC'
        ];
        $sortKey = $filters['sort'] ?? 'newest';
        $orderSql = $sortMap[$sortKey] ?? $sortMap['newest'];

        $where_sql = implode(' AND ', $where_clauses);
        $base_sql = "
            FROM transactions t
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN users fu ON fa.user_id = fu.customer_id
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            LEFT JOIN users tu ON ta.user_id = tu.customer_id
            LEFT JOIN staff s ON t.processed_by_staff_id = s.id
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE {$where_sql}
        ";

        if ($isExport) {
            return $this->repository->exportTransactionsGenerator($base_sql, $params);
        }

        $total = $this->repository->countTransactions($base_sql, $params);
        $pages  = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $transactions = $this->repository->getTransactions($base_sql, $params, $perPage, $offset, $orderSql);
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
    /**
     * Get Users List.
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    private function applyUserFiltersAndSort(array $filters, array &$where_clauses, string &$orderSql): void
    {
        // Status Filter - only active and suspended non-deleted users belong in User Management
        $status = $filters['status'] ?? 'all';
        if ($status === 'active') {
            $where_clauses[] = "u.is_deleted = 0 AND u.status = 'active'";
        } elseif ($status === 'suspended') {
            $where_clauses[] = "u.is_deleted = 0 AND u.status = 'suspended'";
        } else {
            $where_clauses[] = "u.is_deleted = 0 AND u.status IN ('active', 'suspended')";
        }

        // Activity Filter
        if (!empty($filters['activity']) && $filters['activity'] !== 'all') {
            if ($filters['activity'] === 'recent') {
                $where_clauses[] = "u.last_login_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            } elseif ($filters['activity'] === 'inactive_30') {
                $where_clauses[] = "(u.last_login_at < DATE_SUB(NOW(), INTERVAL 30 DAY) OR u.last_login_at IS NULL)";
            } elseif ($filters['activity'] === 'dormant') {
                $where_clauses[] = "(u.last_login_at < DATE_SUB(NOW(), INTERVAL 90 DAY) OR u.last_login_at IS NULL)";
            }
        }

        // Branch Filter
        if (!empty($filters['branch_id']) && $filters['branch_id'] !== 'all' && $filters['branch_id'] !== '0') {
            $where_clauses[] = "u.branch_id = " . intval($filters['branch_id']);
        }

        // Sorting
        if (!empty($filters['sort'])) {
            if ($filters['sort'] === 'newest') {
                $orderSql = "u.created_at DESC";
            } elseif ($filters['sort'] === 'oldest') {
                $orderSql = "u.created_at ASC";
            } elseif ($filters['sort'] === 'name_asc') {
                $orderSql = "u.full_name ASC";
            } elseif ($filters['sort'] === 'recently_active') {
                $orderSql = "u.last_login_at DESC";
            }
        }
    }

    public function getUsersList(array $filters, int $page, int $perPage): array
    {
        $where_clauses = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where_clauses[] = "(u.full_name LIKE ? OR u.customer_id LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $search_param = "%{$filters['search']}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
        }

        $orderSql = "u.created_at DESC";
        $this->applyUserFiltersAndSort($filters, $where_clauses, $orderSql);

        if (empty($where_clauses)) {
            $where_clauses[] = "1=1";
        }

        $where_sql = implode(' AND ', $where_clauses);
        $total = $this->repository->countUsers($where_sql, $params);

        $pages  = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $users = $this->repository->getUsers($where_sql, $params, $perPage, $offset, $orderSql);

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


    /**
     * Get Suspended User Detail.
     *
     * @param int $userId
     * @return array
     */
    public function getSuspendedUserDetail(int $userId): array
    {
        $user = $this->repository->getSuspendedUserProfile($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Suspended user not found'];
        }

        if (!empty($user['id_number'])) {
            try {
                $crypto = new \App\Shared\Security\EncryptionService();
                $user['id_number'] = $crypto->decrypt($user['id_number']);
            } catch (\Throwable $e) {
                // Ignore if decryption fails
            }
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
                if ($tx['type'] === \App\Domain\Enums\TransactionTypeEnum::WITHDRAWAL->value) {
                    $is_debit = true;
                } elseif ($tx['type'] === \App\Domain\Enums\TransactionTypeEnum::TRANSFER->value && $tx['description'] !== 'Deposit') {
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

        $avatar_url = null;
        if (!empty($user['profile_picture'])) {
            $avatar_url = str_starts_with($user['profile_picture'], 'http')
                ? $user['profile_picture']
                : APP_URL . '/' . ltrim($user['profile_picture'], '/');
        } else {
            $base_avatar_dir = defined('UPLOAD_AVATARS_PATH') ? UPLOAD_AVATARS_PATH : (defined('FINZO_ROOT') ? FINZO_ROOT . '/uploads/avatars/' : dirname(__DIR__, 3) . '/public/uploads/avatars/');
            $avatar_path = $base_avatar_dir . 'user_' . $userId . '.jpg';
            if (file_exists($avatar_path)) {
                $avatar_url = APP_URL . '/uploads/avatars/user_' . $userId . '.jpg?t=' . filemtime($avatar_path);
            }
        }

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
            'profile_picture_url' => $avatar_url,
            'profile_picture' => $user['profile_picture'] ?? null,
            'branch_id' => $user['branch_id'] ?? null,
            'branch_name' => $user['branch_name'] ?? 'Unassigned',
            'branch_code' => $user['branch_code'] ?? '',
            'branch_state' => $user['branch_state'] ?? '',
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
    /**
     * Unlock Account.
     *
     * @param int $userId
     * @param int $adminId
     * @return array
     */
    public function unlockAccount(int $userId, int $adminId): array
    {
        $pdo = \App\Shared\Core\Database::getInstance();
        $lower = 'abcdefghjkmnpqrstuvwxyz';
        $upper = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $digits = '23456789';
        $symbols = '@#$%!';
        $chars = $lower . $upper . $digits . $symbols;
        
        $temp_pass = '';
        $temp_pass .= $lower[rand(0, strlen($lower) - 1)];
        $temp_pass .= $upper[rand(0, strlen($upper) - 1)];
        $temp_pass .= $digits[rand(0, strlen($digits) - 1)];
        $temp_pass .= $symbols[rand(0, strlen($symbols) - 1)];
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

        if (function_exists('send_notification') && isset($pdo)) {
            send_notification($pdo, $userId, \App\Domain\Enums\NotificationTypeEnum::SECURITY->value,
                'Account Unlocked',
                'Your account has been unlocked by an administrator. Your password has been reset to a temporary one. Please check with an admin if you did not request this.'
            );
        }

        
            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('account_unlocked', $userId, \App\Domain\Enums\RoleEnum::USER->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "Manually unlocked account and reset password for {$user['full_name']} ({$user['email']}). Previous attempts: {$user['failed_attempts']}")
);
        

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
    /**
     * Process User Action.
     *
     * @param int $userId
     * @param string $action
     * @param string $reason
     * @param string $password
     * @param string $balanceOption
     * @param int $adminId
     * @return array
     */
    public function processUserAction(int $userId, string $action, string $reason, string $password, string $balanceOption, int $adminId): array
    {
        $pdo = \App\Shared\Core\Database::getInstance();
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

        switch ($action) {
            case 'suspend':
                if ($this->security !== null && function_exists('check_admin_permission') && isset($pdo)) {
                    $this->security->checkAdminPermission($adminId, 'suspend_user');
                }
                if ($user['status'] === \App\Domain\Enums\AccountStatusEnum::SUSPENDED->value) {
                    return ['success' => false, 'message' => 'User is already suspended', 'code' => 409];
                }

                $this->repository->suspendUserAccount($userId, $user['customer_id']);

                if (function_exists('send_notification') && isset($pdo)) {
                    send_notification($pdo, $userId, \App\Domain\Enums\NotificationTypeEnum::SECURITY->value,
                        'Account Suspended',
                        'Your account has been suspended. Reason: ' . ($reason ?: 'No reason provided') . '. Contact support for assistance.'
                    );
                }
                
                    \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_suspended', $userId, \App\Domain\Enums\RoleEnum::USER->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "User {$user['full_name']} suspended. Reason: {$reason}")
);
                
                return ['success' => true, 'message' => 'User suspended successfully'];

            case 'reinstate':
                if ($this->security !== null && function_exists('check_admin_permission') && isset($pdo)) {
                    $this->security->checkAdminPermission($adminId, 'reinstate_user');
                }
                if ($user['status'] === \App\Domain\Enums\AccountStatusEnum::ACTIVE->value) {
                    return ['success' => true, 'message' => 'User is already active'];
                }
                if ($user['status'] !== \App\Domain\Enums\AccountStatusEnum::SUSPENDED->value) {
                    return ['success' => false, 'message' => 'User is not suspended', 'code' => 409];
                }

                $this->repository->reinstateUserAccount($userId, $user['customer_id']);

                if (function_exists('send_notification') && isset($pdo)) {
                    send_notification($pdo, $userId, \App\Domain\Enums\NotificationTypeEnum::SECURITY->value, 'Account Reinstated', 'Your account has been reinstated and is now active. You can log in and transact normally.');
                }
                
                    \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_reinstated', $userId, \App\Domain\Enums\RoleEnum::USER->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "User {$user['full_name']} reinstated")
);
                
                return ['success' => true, 'message' => 'User reinstated successfully'];

            case 'delete':
                if ($this->security !== null && function_exists('check_admin_permission') && isset($pdo)) {
                    $this->security->checkAdminPermission($adminId, 'soft_delete_user');
                }
                if ($user['is_deleted']) {
                    return ['success' => true, 'message' => 'User is already deleted'];
                }

                $this->repository->softDeleteUserAccount($userId, $user['customer_id']);

                
                    \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_deleted', $userId, \App\Domain\Enums\RoleEnum::USER->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "User {$user['full_name']} soft-deleted. Reason: {$reason}")
);
                
                return ['success' => true, 'message' => 'User deleted successfully'];

            case 'recover':
                if ($this->security !== null && function_exists('check_admin_permission') && isset($pdo)) {
                    $this->security->checkAdminPermission($adminId, 'soft_delete_user');
                }
                if (!$user['is_deleted']) {
                    return ['success' => false, 'message' => 'User is not deleted', 'code' => 409];
                }

                $this->repository->restoreUserFromSoftDelete($userId);

                if ($balanceOption === 'mandatory') {
                    $account = $this->repository->getUserForActionByCustomer($user['customer_id']);
                    if ($account && $account['balance'] > 500) {
                        $deduction_amount = $account['balance'] - 500;
                        $transactionId = 'TXN' . strtoupper(uniqid());
                        $this->repository->addSystemChargeTransaction($transactionId, $account['id'], $deduction_amount);
                        if (function_exists('send_notification') && isset($pdo)) {
                            send_notification($pdo, $userId, \App\Domain\Enums\NotificationTypeEnum::SYSTEM->value, 'Account Restored – Balance Adjusted', 'Your account has been restored. As per the recovery policy, your balance has been adjusted to the mandatory minimum of ₹500.00. An amount of ₹' . number_format($deduction_amount, 2) . ' was cleared as part of the restore process. This adjustment does not count toward your daily transfer limit.');
                        }
                    } else {
                        if (function_exists('send_notification') && isset($pdo)) {
                            send_notification($pdo, $userId, \App\Domain\Enums\NotificationTypeEnum::SYSTEM->value, 'Account Restored', 'Your account has been restored and is now active with your mandatory minimum balance. You can log in and continue banking.');
                        }
                    }
                    $this->repository->updateAccountBalanceLease500($user['customer_id']);
                } else {
                    $this->repository->activateAccount($user['customer_id']);
                    if (function_exists('send_notification') && isset($pdo)) {
                        send_notification($pdo, $userId, \App\Domain\Enums\NotificationTypeEnum::SYSTEM->value, 'Account Restored', 'Your account has been restored and is now active. Your full balance has been retained. You can log in and continue banking.');
                    }
                }

                
                    \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_recovered', $userId, \App\Domain\Enums\RoleEnum::USER->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "User {$user['full_name']} recovered from soft-delete state" . ($balanceOption === 'mandatory' ? ' with mandatory balance of 500' : ''))
);
                
                return ['success' => true, 'message' => 'User recovered successfully'];

            case 'hard_delete':
                if ($this->security !== null && function_exists('check_admin_permission') && isset($pdo)) {
                    $this->security->checkAdminPermission($adminId, 'soft_delete_user');
                }
                $this->repository->hardDeleteUserAccount($userId, $user['customer_id']);
                
                    \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_hard_deleted', $userId, \App\Domain\Enums\RoleEnum::USER->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "User {$user['full_name']} permanently deleted.")
);
                
                return ['success' => true, 'message' => 'User permanently deleted'];

            case 'delete_account_only':
                if ($this->security !== null && function_exists('check_admin_permission') && isset($pdo)) {
                    $this->security->checkAdminPermission($adminId, 'soft_delete_user');
                }
                $this->repository->deleteAccountOnly($user['customer_id']);
                
                    \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('account_hard_deleted', $userId, \App\Domain\Enums\RoleEnum::USER->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "Account for user {$user['full_name']} permanently deleted.")
);
                
                return ['success' => true, 'message' => 'Account permanently deleted'];
        }
        return ['success' => false, 'message' => 'Action failed', 'code' => 500];
    }
    /**
     * Get User Detail.
     *
     * @param int $userId
     * @return array
     */
    public function getUserDetail(int $userId): array
    {
        $user = $this->repository->getUserProfile($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if (!empty($user['id_number'])) {
            try {
                $crypto = new \App\Shared\Security\EncryptionService();
                $user['id_number'] = $crypto->decrypt($user['id_number']);
            } catch (\Throwable $e) {
                // Ignore if decryption fails
            }
        }

        $accounts = $this->repository->getUserAccounts($user['customer_id']);
        $transactions = [];
        if (count($accounts) > 0) {
            $account_ids = array_column($accounts, 'id');
            $transactions = $this->repository->getAccountsRecentTransactions($account_ids, 20);
        }

        $id_front_url = null;
        if (!empty($user['id_front_path'])) {
            $id_front_url = str_starts_with($user['id_front_path'], 'storage/')
                ? APP_URL . '/api/v1/users/' . $user['id'] . '/kyc?path=' . urlencode($user['id_front_path'])
                : (str_starts_with($user['id_front_path'], 'http') ? $user['id_front_path'] : APP_URL . '/' . ltrim($user['id_front_path'], '/'));
        }

        $id_back_url = null;
        if (!empty($user['id_back_path'])) {
            $id_back_url = str_starts_with($user['id_back_path'], 'storage/')
                ? APP_URL . '/api/v1/users/' . $user['id'] . '/kyc?path=' . urlencode($user['id_back_path'])
                : (str_starts_with($user['id_back_path'], 'http') ? $user['id_back_path'] : APP_URL . '/' . ltrim($user['id_back_path'], '/'));
        }

        $avatar_url = !empty($user['profile_picture'])
            ? (str_starts_with($user['profile_picture'], 'http') ? $user['profile_picture'] : APP_URL . '/' . ltrim($user['profile_picture'], '/'))
            : null;
        if (!$avatar_url) {
            $avatar_path = 'public/uploads/avatars/user_' . $user['id'] . '.jpg';
            if (file_exists(FINZO_ROOT . '/' . $avatar_path)) {
                $avatar_url = APP_URL . '/uploads/avatars/user_' . $user['id'] . '.jpg';
            }
        }

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
    public function reverseTransaction(string $transactionId, int $adminId, string $reason): array
    {
        $pdo = \App\Shared\Core\Database::getInstance();
        try {
            $this->repository->beginTransaction();
            $txn = $this->repository->getTransactionForReversal($transactionId);
            if (!$txn) {
                return ['success' => false, 'message' => 'Transaction not found or already locked', 'code' => 404];
            }
            if ($txn['status'] === 'reversed') {
                return ['success' => false, 'message' => 'Transaction is already reversed', 'code' => 422];
            }
            if ($txn['status'] !== 'completed') {
                return ['success' => false, 'message' => 'Only completed transactions can be reversed', 'code' => 422];
            }

            $source_id = $txn['from_account_id'];
            $dest_id = $txn['to_account_id'];
            $amount = (float)$txn['amount'];

            // We need to reverse the flow. The receiver must have enough funds to be debited.
            $first_id = null;
            $second_id = null;
            if ($source_id && $dest_id) {
                $first_id = min($source_id, $dest_id);
                $second_id = max($source_id, $dest_id);
                $first = $this->repository->getAccountForUpdate($first_id);
                $second = $this->repository->getAccountForUpdate($second_id);
                $source = ($first['id'] == $source_id) ? $first : $second;
                $dest = ($first['id'] == $dest_id) ? $first : $second;
            } elseif ($source_id) {
                $source = $this->repository->getAccountForUpdate($source_id);
                $dest = null;
            } elseif ($dest_id) {
                $dest = $this->repository->getAccountForUpdate($dest_id);
                $source = null;
            }

            $new_source_balance = $source ? $source['balance'] : null;
            $new_dest_balance = $dest ? $dest['balance'] : null;

            if ($txn['type'] === 'deposit' && $dest) {
                if ($dest['balance'] < $amount) {
                    return ['success' => false, 'message' => 'Account has insufficient funds to reverse this deposit', 'code' => 422];
                }
                $new_dest_balance = $dest['balance'] - $amount;
                $this->repository->updateAccountBalance($new_dest_balance, $dest['id']);
            } elseif ($txn['type'] === 'withdrawal' && $source) {
                $new_source_balance = $source['balance'] + $amount;
                $this->repository->updateAccountBalance($new_source_balance, $source['id']);
            } elseif ($txn['type'] === 'transfer' && $source && $dest) {
                if ($dest['balance'] < $amount) {
                    return ['success' => false, 'message' => 'Destination account has insufficient funds to reverse this transfer', 'code' => 422];
                }
                $new_dest_balance = $dest['balance'] - $amount;
                $this->repository->updateAccountBalance($new_dest_balance, $dest['id']);
                
                $new_source_balance = $source['balance'] + $amount;
                $this->repository->updateAccountBalance($new_source_balance, $source['id']);
            } else {
                return ['success' => false, 'message' => 'Unsupported transaction type for reversal', 'code' => 422];
            }

            // Mark original as reversed
            $pdo->prepare("UPDATE transactions SET status = 'reversed' WHERE id = ?")->execute([$txn['id']]);

            // Create Reversal transaction
            $revTxnId = generate_transaction_id();
            $this->repository->addTransaction(
                $revTxnId,
                $dest ? $dest['id'] : null, // Reversing flow
                $source ? $source['id'] : null,
                'transfer', // Store as transfer or just reversed depending on type. But we'll use original type in reference.
                $amount,
                $new_dest_balance ?? 0,
                "Reversal of {$transactionId}",
                "Admin Reason: {$reason}",
                'completed'
            );

            // Update destination balance_after for the new transaction properly
            $pdo->prepare("UPDATE transactions SET dest_balance_after = ? WHERE txn_id = ?")->execute([$new_source_balance, $revTxnId]);

            // Write Audit
            if ($this->security) {
                $this->security->writeAuditLog(
                    \App\Domain\Enums\AuditActionEnum::ADMIN_ACTION->value,
                    $txn['id'],
                    'transaction',
                    $adminId,
                    \App\Domain\Enums\RoleEnum::ADMIN->value,
                    "Reversed transaction {$transactionId}. Reason: {$reason}"
                );
            }
            $targetCustomerId = $dest ? ($dest['customer_id'] ?? null) : ($source ? ($source['customer_id'] ?? null) : null);
            if ($targetCustomerId) {
                $this->repository->addNotification($targetCustomerId, 'transaction', 'Transaction Reversed', 'A transaction of ₹' . number_format($txn['amount'], 2) . ' was reversed by administration. Ref: ' . $transactionId);
            }

            $this->repository->commit();
            return ['success' => true, 'message' => 'Transaction reversed successfully', 'code' => 200];
        } catch (\Throwable $e) {
            if ($this->repository->inTransaction()) {
                $this->repository->rollBack();
            }
            \App\Shared\Core\Logging\Logger::getInstance()->error('Reversal error: ', ['context' => $e->getMessage()]);
            return ['success' => false, 'message' => 'An error occurred during reversal', 'code' => 500];
        }
    }
}
