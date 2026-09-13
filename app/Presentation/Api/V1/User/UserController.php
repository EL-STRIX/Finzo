<?php

namespace App\Presentation\Api\V1\User;

use App\Shared\Security\SecurityService;

use App\Application\Services\UserService;
use App\Infrastructure\Repositories\UserRepository;
use PDOException;
use Exception;

class UserController
{
    protected SecurityService $security;

    protected UserService $service;
    protected UserRepository $repository;

    public function __construct(\App\Domain\Contracts\Services\UserServiceInterface $service, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security;
        $this->service = $service;
        $this->repository = $service->getRepository();
    }

    public function accountLookup()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $accountNumber = $this->security->cleanInput($_GET['account_no'] ?? '');
        if (\App\Shared\Core\Validation\Validator::isEmpty($accountNumber)) {
            json_response(['found' => false, 'message' => 'Account number is required'], 422);
        }
        try {
            $result = $this->service->fetchAccountLookup($accountNumber);
            if (!$result || $result['status'] !== \App\Domain\Enums\AccountStatusEnum::ACTIVE->value) {
                json_response(['success' => false, 'message' => 'Finzo account not found'], 404);
            }
            $userId = $_SESSION['user_id'] ?? null;
            $isSelf = false;
            if ($userId) {
                try {
                    $ownAccount = $this->service->fetchAccountDetails($userId);
                    if ($ownAccount && strcasecmp($ownAccount['account_no'] ?? '', $accountNumber) === 0) {
                        $isSelf = true;
                    }
                } catch (\Throwable $e) {}
            }
            json_response([
                'success' => true,
                'account' => [
                    'holder_name'  => $result['full_name'],
                    'account_type' => $result['account_type'],
                    'is_self'      => $isSelf
                ]
            ]);
        } catch (PDOException $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Account lookup error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Lookup failed'], 500);
        }
    }

    public function accountSettings()
    {
        if (empty($_SESSION['user_id'])) {
            json_response(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $userId = (int)$_SESSION['user_id'];
        try {
            $account = $this->service->fetchAccountSettings($userId);
            if (!$account) {
                json_response(['success' => false, 'message' => 'No active account found'], 404);
            }
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $used_today = (float)$this->service->fetchUsedToday($account['id']);
                $daily_limit = (float)$account['daily_limit'];
                json_response([
                    'success' => true,
                    'daily_limit' => $daily_limit,
                    'used_today' => $used_today,
                    'remaining_limit' => max(0, $daily_limit - $used_today)
                ]);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                $daily_limit = $input['daily_limit'] ?? $_POST['daily_limit'] ?? '';
                $daily_limit = $this->security->cleanInput($daily_limit);
                if (!is_numeric($daily_limit) || $daily_limit < 1000 || $daily_limit > 100000) {
                    json_response(['success' => false, 'message' => 'Invalid limit value. Must be between ₹1,000 and ₹1,00,000'], 422);
                }
                $this->service->updateDailyLimit($account['id'], (float)$daily_limit);
                json_response([
                    'success' => true, 
                    'message' => 'Daily limit updated successfully',
                    'daily_limit' => (float)$daily_limit
                ]);
            } else {
                json_response(['success' => false, 'message' => 'Method not allowed'], 405);
            }
        } catch (PDOException $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Account settings API error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    public function accounts()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        if (empty($_SESSION['user_id'])) {
            json_response(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $userId = (int)$_SESSION['user_id'];
        try {
            $userStatus = $_SESSION['user_status'] ?? 'pending';
            if ($userStatus !== 'active') {
                json_response(['success' => true, 'account' => null, 'message' => 'No active account found']);
            }
            $account = $this->service->fetchAccountDetails($userId);
            if (!$account) {
                json_response(['success' => true, 'account' => null, 'message' => 'No account found']);
            }
            $mini_statement = [];
            if ($account['status'] === \App\Domain\Enums\AccountStatusEnum::ACTIVE->value) {
                $mini_statement = $this->service->fetchMiniStatement($account['id']);
                foreach ($mini_statement as &$tx) {
                    $tx['direction'] = ((int)($tx['to_account_id'] ?? 0) === (int)$account['id']) ? 'credit' : 'debit';
                }
                unset($tx);
            }
            $today_transfer_total = (float)$this->service->fetchTodayTransferTotal($account['id']);
            json_response([
                'success' => true,
                'account' => [
                    'id'             => (int)$account['id'],
                    'account_no'     => $account['account_no'],
                    'account_type'   => $account['account_type'],
                    'balance'        => (float)$account['balance'],
                    'interest_rate'  => (float)$account['interest_rate'],
                    'daily_limit'    => (float)$account['daily_limit'],
                    'status'         => $account['status'],
                    'opened_date'    => $account['opened_date'],
                    'holder_name'    => $account['full_name'],
                    'email'          => $account['email'],
                    'phone'          => $account['phone']
                ],
                'mini_statement'       => $mini_statement,
                'today_transfer_total' => $today_transfer_total,
                'remaining_limit'      => max(0, (float)$account['daily_limit'] - $today_transfer_total)
            ]);
        } catch (PDOException $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Accounts API error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to load account details'], 500);
        }
    }

    public function beneficiary()
    {
        $userId = $_SESSION['user_customer_id'] ?? null;
        if (!$userId && !empty($_SESSION['user_id'])) {
            $userId = $this->service->getCustomerId((int)$_SESSION['user_id']);
        }
        if (!$userId) {
            $userId = $_SESSION['user_id'] ?? null;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                $beneficiaries = $this->service->fetchBeneficiaries($userId);
                json_response(['success' => true, 'beneficiaries' => $beneficiaries, 'total' => count($beneficiaries)]);
            } catch (PDOException $e) {
                \App\Shared\Core\Logging\Logger::getInstance()->error('Beneficiaries fetch error: ', ['context' => $e->getMessage()]);
                json_response(['success' => false, 'message' => 'Failed to load beneficiaries'], 500);
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true) ?? [];
            $postData = !empty($input) ? array_merge($_POST, $input) : $_POST;

            $action = $this->security->cleanInput($postData['action'] ?? 'add');
            if ($action === 'delete') {
                $beneficiary_id = intval($postData['beneficiary_id'] ?? 0);
                if (!$beneficiary_id) {
                    json_response(['success' => false, 'message' => 'Beneficiary ID is required'], 422);
                }
                try {
                    $deleted = $this->service->deleteBeneficiary($beneficiary_id, $userId);
                    if ($deleted === 0) {
                        json_response(['success' => false, 'message' => 'Beneficiary not found'], 404);
                    }
                    json_response(['success' => true, 'message' => 'Beneficiary removed successfully']);
                } catch (PDOException $e) {
                    \App\Shared\Core\Logging\Logger::getInstance()->error('Delete beneficiary error', ['context' => $e->getMessage()]);
                    json_response(['success' => false, 'message' => 'Failed to remove beneficiary'], 500);
                }
            }

            try {
                $validator = new \App\Presentation\Validators\UserValidator($this->service);
                $validated = $validator->validateBeneficiary($postData);
            } catch (\App\Shared\Core\Exceptions\ValidationException $e) {
                $errors = $e->getPayload()['errors'] ?? [];
                $firstError = !empty($errors) ? current($errors) : $e->getMessage();
                json_response(['success' => false, 'message' => $firstError, 'errors' => $errors], 422);
            }

            $nickname = $validated['nickname'];
            $accountNumber = $validated['account_no'];
            $bank_name = $this->security->cleanInput($postData['bank_name'] ?? 'Finzo Banking System');

            try {
                $targetAccount = $this->service->fetchAccountLookup($accountNumber);
                if (!$targetAccount || $targetAccount['status'] !== \App\Domain\Enums\AccountStatusEnum::ACTIVE->value) {
                    json_response(['success' => false, 'message' => 'Recipient account not found or is inactive'], 404);
                }

                $rawSessionUserId = $_SESSION['user_id'] ?? 0;
                if ($rawSessionUserId) {
                    try {
                        $ownAcc = $this->service->fetchAccountDetails($rawSessionUserId);
                        if ($ownAcc && strcasecmp($ownAcc['account_no'] ?? '', $accountNumber) === 0) {
                            json_response(['success' => false, 'message' => 'Cannot add your own account as a beneficiary'], 422);
                        }
                    } catch (\Throwable $e) {}
                }

                $max_beneficiaries = get_setting($this->service->getDb(), 'max_beneficiaries', 10);
                $count = $this->service->countBeneficiaries($userId);
                if ($count >= $max_beneficiaries) {
                    json_response(['success' => false, 'message' => 'Maximum limit of ' . $max_beneficiaries . ' beneficiaries reached'], 403);
                }
                $existing = $this->service->getBeneficiaryByAccount($userId, $accountNumber);
                if ($existing) {
                    json_response(['success' => false, 'message' => 'This beneficiary already exists'], 409);
                }
                $new_id = $this->service->addBeneficiary($userId, $nickname, $accountNumber, $bank_name);
                json_response([
                    'success' => true,
                    'message' => 'Beneficiary added successfully',
                    'beneficiary' => ['id' => (int)$new_id, 'nickname' => $nickname, 'account_no' => $accountNumber, 'bank_name' => $bank_name]
                ], 201);
            } catch (PDOException $e) {
                \App\Shared\Core\Logging\Logger::getInstance()->error('Add beneficiary error', ['context' => $e->getMessage()]);
                json_response(['success' => false, 'message' => 'Failed to add beneficiary'], 500);
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $rawInput = file_get_contents('php://input');
            $delete_data = json_decode($rawInput, true) ?? [];
            if (empty($delete_data)) {
                parse_str($rawInput, $delete_data);
            }
            $beneficiary_id = intval($delete_data['beneficiary_id'] ?? $_POST['beneficiary_id'] ?? $_GET['id'] ?? 0);
            if (!$beneficiary_id) {
                json_response(['success' => false, 'message' => 'Beneficiary ID is required'], 422);
            }
            try {
                $deleted = $this->service->deleteBeneficiary($beneficiary_id, $userId);
                if ($deleted === 0) {
                    json_response(['success' => false, 'message' => 'Beneficiary not found'], 404);
                }
                json_response(['success' => true, 'message' => 'Beneficiary removed successfully']);
            } catch (PDOException $e) {
                \App\Shared\Core\Logging\Logger::getInstance()->error('Delete beneficiary error', ['context' => $e->getMessage()]);
                json_response(['success' => false, 'message' => 'Failed to remove beneficiary'], 500);
            }
        }
        json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $userId = $_SESSION['user_id'];
        $validator = new \App\Presentation\Validators\UserValidator($this->service);
        $validated = $validator->validatePasswordUpdate($_POST);
        $currentPassword = $validated['current_password'];
        $newPassword = $validated['new_password'];


        try {
            $user = $this->service->getUserPasswordHash($userId);
            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                json_response(['success' => false, 'errors' => ['current_password' => 'Current password is incorrect']], 401);
            }
            $new_hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $this->service->updatePassword($userId, $new_hash);
            send_notification($this->service->getDb(), $user['customer_id'], \App\Domain\Enums\NotificationTypeEnum::SECURITY->value, 'Password Changed', 'Your account password was changed successfully. If you did not make this change, please contact support immediately.');

            \App\Shared\Security\SessionManager::destroy();

            json_response(['success' => true, 'message' => 'Password updated successfully. Please log in again.']);
        } catch (PDOException $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Change password error', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to change password'], 500);
        }
    }

    public function completeProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        if (empty($_SESSION['user_id'])) {
            json_response(['success' => false, 'message' => 'Your session has expired. Please log in again.', 'redirect' => '/Finzo/public/views/auth/login.php'], 401);
        }

        $userId = (int)$_SESSION['user_id'];
        try {
            $dto = new \App\Application\DTOs\KycRequestDTO($userId, $_POST, $_FILES);
            $action = new \App\Application\Actions\CompleteUserProfileAction($this->service, $this->security);
            $action->execute($dto);
            json_response(['success'  => true, 'message'  => 'Your profile has been submitted for review. We will notify you once it is processed.', 'redirect' => '/public/views/user/dashboard.html']);
        } catch (\App\Shared\Core\Exceptions\ApiException $e) {
            json_response(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->getPayload()], $e->getCode());
        } catch (\Throwable $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Complete profile error', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'An error occurred while processing your application. Please try again.'], 500);
        }
    }

    public function dashboard()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        if (empty($_SESSION['user_id'])) {
            json_response(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $userId = (int)$_SESSION['user_id'];
        try {
            $user = $this->service->getUserDashboardData($userId);
            if (!$user) {
                destroy_session();
                json_response(['success' => false, 'message' => 'User not found', 'redirect' => '/Finzo/public/views/auth/login.php'], 404);
            }
            $_SESSION['user_status'] = $user['status'];
            $account = null;
            $recent_transactions = [];
            $monthly_stats = ['income_total' => 0, 'expense_total' => 0, 'month_label' => date('F Y')];

            if ($user['status'] === 'active') {
                $account = $this->service->getActiveAccountByCustomerId($user['customer_id']);
                if ($account && $account['status'] === \App\Domain\Enums\AccountStatusEnum::ACTIVE->value) {
                    $recent_transactions = $this->service->getRecentTransactions($account['id']);
                    foreach ($recent_transactions as &$txn) {
                        $isIncoming = ((int)($txn['to_account_id'] ?? 0) === (int)$account['id']);
                        $txn['direction'] = $isIncoming ? 'credit' : 'debit';
                    }
                    unset($txn);

                    $monthly_stats['income_total'] = (float)$this->service->getMonthlyIncome($account['id']);
                    $monthly_stats['expense_total'] = (float)$this->service->getMonthlyExpenses($account['id']);
                }
            }
            if (!\App\Shared\Core\Validation\Validator::isSet($_SESSION['user_customer_id'])) {
                $_SESSION['user_customer_id'] = $this->service->getCustomerId($userId);
            }
            $customer_id = $_SESSION['user_customer_id'];
            $notification_count = (int)$this->service->getUnreadNotificationCount($customer_id);
            $greeting = get_greeting();
            $rawPic = $user['profile_picture'] ?? null;
            if (!empty($rawPic)) {
                if (str_starts_with($rawPic, 'http')) {
                    $avatar_url = $rawPic;
                } else {
                    $cleanPic = ltrim($rawPic, '/');
                    // Stored paths start with 'uploads/' or 'storage/' — files live under public/
                    if (!str_starts_with($cleanPic, 'public/')) {
                        $cleanPic = 'public/' . $cleanPic;
                    }
                    $avatar_url = APP_URL . '/' . $cleanPic;
                }
            } else {
                $avatar_url = null;
            }
            // Keep session in sync so PHP sidebar renders photo immediately on next page load
            $_SESSION['user_avatar'] = $avatar_url;

            $past_rejections_raw = $this->service->getPastRejections($user['customer_id']);
            $past_rejections = [];
            foreach ($past_rejections_raw as $log) {
                $reason = "Application was rejected";
                if (\App\Shared\Core\Validation\Validator::pregMatch('/Reason:\s*(.+)$/i', $log['details'], $matches)) {
                    $reason = trim($matches[1]);
                }
                $past_rejections[] = ['reason' => $reason, 'rejected_at' => $log['created_at']];
            }
            json_response([
                'success' => true,
                'user' => ['id' => (int)$user['id'], 'customer_id' => $user['customer_id'], 'name' => $user['full_name'], 'email' => $user['email'], 'phone' => $user['phone'], 'status' => $user['status'], 'rejection_reason' => $user['rejection_reason'], 'updated_at' => $user['updated_at'], 'member_since' => $user['created_at'], 'avatar_url' => $avatar_url],
                'account' => $account ? ['account_no' => $account['account_no'], 'account_type' => $account['account_type'], 'balance' => (float)$account['balance'], 'interest_rate' => (float)$account['interest_rate'], 'daily_limit' => (float)$account['daily_limit'], 'status' => $account['status'], 'opened_date' => $account['opened_date']] : null,
                'past_rejections' => $past_rejections, 'recent_transactions' => $recent_transactions, 'monthly_stats' => $monthly_stats, 'notification_count' => $notification_count, 'greeting' => $greeting
            ]);
        } catch (PDOException $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Dashboard error', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to load dashboard data'], 500);
        }
    }

    public function notifications()
    {
        if (!\App\Shared\Core\Validation\Validator::isSet($_SESSION['user_customer_id'])) {
            $_SESSION['user_customer_id'] = $this->service->getCustomerId($_SESSION['user_id']);
        }
        $userId = $_SESSION['user_customer_id'];
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                $page = max(1, intval($_GET['page'] ?? 1));
                $per_page = min(50, max(1, intval($_GET['per_page'] ?? 20)));
                $offset = ($page - 1) * $per_page;
                $total = (int)$this->service->getNotificationsTotal($userId);
                $unread_count = (int)$this->service->getUnreadNotificationCount($userId);
                $notifications = $this->service->getNotifications($userId, $per_page, $offset);
                json_response(['success' => true, 'notifications' => $notifications, 'unread_count' => $unread_count, 'total' => $total, 'pages' => max(1, ceil($total / $per_page)), 'current_page' => $page]);
            } catch (PDOException $e) {
                \App\Shared\Core\Logging\Logger::getInstance()->error('Notifications API error (GET)', ['context' => $e->getMessage()]);
                json_response(['success' => false, 'message' => 'Failed to load notifications'], 500);
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->security->cleanInput($_POST['action'] ?? '');
            try {
                if ($action === 'mark_read') {
                    $notification_id = intval($_POST['notification_id'] ?? 0);
                    if (!$notification_id) {
                        json_response(['success' => false, 'message' => 'Notification ID is required'], 422);
                    }
                    $this->service->markNotificationRead($notification_id, $userId);
                    json_response(['success' => true, 'message' => 'Notification marked as read']);
                } elseif ($action === 'mark_all_read') {
                    $updated = $this->service->markAllNotificationsRead($userId);
                    json_response(['success' => true, 'message' => 'All notifications marked as read', 'updated' => $updated]);
                } elseif ($action === 'delete') {
                    $notification_id = intval($_POST['notification_id'] ?? 0);
                    if (!$notification_id) {
                        json_response(['success' => false, 'message' => 'Notification ID is required'], 422);
                    }
                    $this->service->deleteNotification($notification_id, $userId);
                    json_response(['success' => true, 'message' => 'Notification deleted']);
                } elseif ($action === 'clear_all') {
                    $deleted = $this->service->clearAllNotifications($userId);
                    json_response(['success' => true, 'message' => 'All notifications cleared', 'deleted' => $deleted]);
                } else {
                    json_response(['success' => false, 'message' => 'Invalid action'], 422);
                }
            } catch (PDOException $e) {
                \App\Shared\Core\Logging\Logger::getInstance()->error('Notifications API error (POST)', ['context' => $e->getMessage()]);
                json_response(['success' => false, 'message' => 'Failed to update notifications'], 500);
            }
        }
        json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    public function profile()
    {
        $userId = $_SESSION['user_id'];
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                $user = $this->service->getFullProfile($userId);
                if (!$user) {
                    json_response(['success' => false, 'message' => 'User not found'], 404);
                }
                $account = null;
                if ($user['status'] === 'active') {
                    $account = $this->service->getActiveAccountByCustomerId($user['customer_id']);
                }
                json_response(['success' => true, 'user' => $user, 'account' => $account]);
            } catch (PDOException $e) {
                \App\Shared\Core\Logging\Logger::getInstance()->error('Profile API error (GET)', ['context' => $e->getMessage()]);
                json_response(['success' => false, 'message' => 'Failed to load profile'], 500);
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $validator = new \App\Presentation\Validators\UserValidator($this->service);
            $validated = $validator->validateProfileUpdate($_POST, $userId, $_SESSION['user_email'] ?? '');
            $fullName = $validated['full_name'];
            $phone = $validated['phone'];
            $email = $validated['email'];

            try {
                $this->service->updateProfile($userId, $fullName, $phone, $email);
                $_SESSION['user_name'] = $fullName;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_phone'] = $phone;
                json_response(['success' => true, 'message' => 'Profile updated successfully']);
            } catch (PDOException $e) {
                \App\Shared\Core\Logging\Logger::getInstance()->error('Profile update error', ['context' => $e->getMessage()]);
                json_response(['success' => false, 'message' => 'Failed to update profile'], 500);
            }
        }
        json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    public function statement()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $userId = $_SESSION['user_id'];
        try {
            $account = $this->service->getActiveAccount($userId);
            if (!$account) {
                json_response(['success' => false, 'message' => 'No active account found'], 404);
            }
            $fromDate = $this->security->cleanInput($_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days')));
            $toDate = $this->security->cleanInput($_GET['to_date'] ?? date('Y-m-d'));
            $transactions = $this->service->getStatementTransactions($account['id'], $fromDate, $toDate);
            $opening_row = $this->service->getOpeningBalance($account['id'], $fromDate);
            $opening_balance = $opening_row ? (float)$opening_row['balance_after'] : 0;
            foreach ($transactions as &$txn) {
                if (in_array($txn['type'], ['deposit', 'interest_credit']) || ($txn['type'] === \App\Domain\Enums\TransactionTypeEnum::TRANSFER->value && $txn['to_account_id'] == $account['id'])) {
                    $txn['direction'] = 'credit';
                    $txn['debit'] = 0;
                    $txn['credit'] = (float)$txn['amount'];
                } else {
                    $txn['direction'] = 'debit';
                    $txn['debit'] = (float)$txn['amount'];
                    $txn['credit'] = 0;
                }
            }
            unset($txn);
            $total_credits = array_sum(array_column($transactions, 'credit'));
            $total_debits = array_sum(array_column($transactions, 'debit'));
            $closing_balance = (float)$account['balance'];
            json_response(['success' => true, 'account' => ['account_no' => $account['account_no'], 'account_type' => $account['account_type'], 'holder_name' => $account['full_name']], 'statement' => ['from_date' => $fromDate, 'to_date' => $toDate, 'opening_balance' => $opening_balance, 'closing_balance' => $closing_balance, 'total_credits' => $total_credits, 'total_debits' => $total_debits, 'transactions' => $transactions]]);
        } catch (PDOException $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Statement generation error', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to generate statement'], 500);
        }
    }

    public function transactions()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $userId = $_SESSION['user_id'];
        try {
            $account = $this->service->fetchAccountSettings($userId);
            if (!$account) {
                json_response(['success' => true, 'transactions' => [], 'total' => 0, 'pages' => 0, 'current_page' => 1, 'message' => 'No active account found']);
            }
            $account_id = $account['id'];
            $filters = [
                'type' => $this->security->cleanInput($_GET['type'] ?? 'all'),
                'status' => $this->security->cleanInput($_GET['status'] ?? 'all'),
                'from_date' => $this->security->cleanInput($_GET['from_date'] ?? ''),
                'to_date' => $this->security->cleanInput($_GET['to_date'] ?? ''),
                'search' => $this->security->cleanInput($_GET['search'] ?? ''),
                'page' => max(1, intval($_GET['page'] ?? 1)),
                'per_page' => min(50, max(10, intval($_GET['per_page'] ?? 20)))
            ];

            $data = $this->service->getTransactions($account_id, $filters);
            json_response([
                'success' => true, 
                'transactions' => $data['transactions'], 
                'total' => $data['total'], 
                'pages' => $data['pages'], 
                'current_page' => $data['current_page'], 
                'per_page' => $data['per_page']
            ]);
        } catch (PDOException $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Transactions fetch error', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to load transactions'], 500);
        }
    }

    public function transfer()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        if (empty($_SESSION['user_id'])) {
            json_response(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (($_SESSION['user_status'] ?? '') !== \App\Domain\Enums\AccountStatusEnum::ACTIVE->value) {
            json_response(['success' => false, 'message' => 'Your account is not active. Transfers are not allowed.'], 403);
        }

        $userId = (int)$_SESSION['user_id'];
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true) ?? [];
        $postData = !empty($input) ? array_merge($_POST, $input) : $_POST;

        try {
            $validator = new \App\Presentation\Validators\UserValidator($this->service);
            $validated = $validator->validateTransfer($postData);

            $dto = new \App\Application\DTOs\TransferRequestDTO($userId, $validated);
            $action = new \App\Application\Actions\ProcessTransferAction($this->service, $this->security);
            $result = $action->execute($dto);
            $_SESSION['user_balance'] = $result['new_balance'];
            
            json_response([
                'success' => true, 
                'message' => 'Transfer successful', 
                'transaction_id' => $result['transaction_id'], 
                'amount' => $result['amount'], 
                'to_name' => $result['to_name'], 
                'to_account' => $result['to_account'], 
                'new_balance' => $result['new_balance'], 
                'redirect' => '/Finzo/public/views/user/receipt.php?id=' . $result['transaction_id'] . '&success=1'
            ]);
        } catch (\App\Shared\Core\Exceptions\ValidationException $e) {
            json_response([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => method_exists($e, 'getPayload') ? ($e->getPayload()['errors'] ?? []) : []
            ], 422);
        } catch (\App\Shared\Core\Exceptions\ApiException $e) {
            json_response(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->getPayload()], $e->getCode() ?: 400);
        } catch (\App\Shared\Core\Exceptions\BusinessRuleException $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\App\Shared\Core\Exceptions\ResourceNotFoundException $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Transfer error', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'An error occurred while processing your transfer. Please try again.'], 500);
        }
    }

    public function verifyPin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        $pin = $input['pin'] ?? '';
        if (\App\Shared\Core\Validation\Validator::isEmpty($pin) || \App\Shared\Core\Validation\Validator::length($pin) !== 4 || !is_numeric($pin)) {
            json_response(['success' => false, 'message' => 'Invalid PIN format'], 400);
        }
        $pdo = $this->service->getDb();
        if (!validate_pin($pdo, $_SESSION['user_id'], $pin)) {
            json_response(['success' => false, 'message' => 'Incorrect PIN'], 401);
        }
        json_response(['success' => true, 'message' => 'PIN verified successfully']);
    }
}
