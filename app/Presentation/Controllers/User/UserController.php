<?php

namespace App\Presentation\Controllers\User;

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

    public function __construct(UserService $service, ?SecurityService $security = null)
    {
        $this->security = $security ?? new \App\Shared\Security\SecurityService();
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
            if (!$result || $result['status'] !== 'active') {
                json_response(['success' => false, 'message' => 'Finzo account not found']);
            }
            json_response([
                'success' => true,
                'account' => [
                    'holder_name'  => $result['full_name'],
                    'account_type' => $result['account_type']
                ]
            ]);
        } catch (PDOException $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Account lookup error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Lookup failed'], 500);
        }
    }

    public function accountSettings()
    {
        $userId = $_SESSION['user_id'];
        try {
            $account = $this->service->fetchAccountSettings($userId);
            if (!$account) {
                json_response(['success' => false, 'message' => 'No active account found'], 404);
            }
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $used_today = $this->service->fetchUsedToday($account['id']);
                json_response([
                    'success' => true,
                    'daily_limit' => (float)$account['daily_limit'],
                    'used_today' => (float)$used_today
                ]);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $daily_limit = $this->security->cleanInput($_POST['daily_limit'] ?? '');
                if (!is_numeric($daily_limit) || $daily_limit < 1000 || $daily_limit > 100000) {
                    json_response(['success' => false, 'message' => 'Invalid limit value. Must be between 1,000 and 1,00,000']);
                }
                $this->service->updateDailyLimit($account['id'], $daily_limit);
                json_response(['success' => true, 'message' => 'Daily limit updated']);
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
        $userId = $_SESSION['user_id'];
        try {
            $account = $this->service->fetchAccountDetails($userId);
            if (!$account) {
                json_response(['success' => true, 'account' => null, 'message' => 'No account found']);
            }
            $mini_statement = [];
            if ($account['status'] === 'active') {
                $mini_statement = $this->service->fetchMiniStatement($account['id']);
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
        $userId = $_SESSION['user_id'];
        $customer_id = $this->service->getCustomerId($userId);
        $userId = $customer_id ?: $userId;

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
            $action = $this->security->cleanInput($_POST['action'] ?? 'add');
            if ($action === 'delete') {
                $beneficiary_id = intval($_POST['beneficiary_id'] ?? 0);
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
                    json_response(['success' => false, 'message' => 'Failed to remove beneficiary'], 500);
                }
            }
            $nickname   = $this->security->cleanInput($_POST['nickname'] ?? '');
            $accountNumber = $this->security->cleanInput($_POST['account_no'] ?? '');
            $bank_name  = $this->security->cleanInput($_POST['bank_name'] ?? 'Finzo');
            $errors = [];
            if (\App\Shared\Core\Validation\Validator::isEmpty($nickname) || \App\Shared\Core\Validation\Validator::length($nickname) < 2 || \App\Shared\Core\Validation\Validator::length($nickname) > 100) {
                $errors['nickname'] = 'Nickname must be between 2 and 100 characters';
            }
            if (\App\Shared\Core\Validation\Validator::isEmpty($accountNumber)) {
                $errors['account_no'] = 'Account number is required';
            }
            if (!\App\Shared\Core\Validation\Validator::isEmpty($errors)) {
                json_response(['success' => false, 'errors' => $errors], 422);
            }
            try {
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
                json_response(['success' => false, 'message' => 'Failed to add beneficiary'], 500);
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            parse_str(file_get_contents('php://input'), $delete_data);
            $beneficiary_id = intval($delete_data['beneficiary_id'] ?? $_GET['id'] ?? 0);
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
        if (!$this->security->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            json_response(['success' => false, 'message' => 'Invalid security token'], 403);
        }
        $userId = $_SESSION['user_id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $errors = [];
        if (\App\Shared\Core\Validation\Validator::isEmpty($currentPassword)) {
            $errors['current_password'] = 'Current password is required';
        }
        if (\App\Shared\Core\Validation\Validator::isEmpty($newPassword)) {
            $errors['new_password'] = 'New password is required';
        } else {
            $strength = validate_password_strength($newPassword);
            if (!$strength['valid']) {
                $errors['new_password'] = $strength['message'];
            }
        }
        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        if ($currentPassword === $newPassword && !\App\Shared\Core\Validation\Validator::isEmpty($newPassword)) {
            $errors['new_password'] = 'New password must be different from current password';
        }
        if (!\App\Shared\Core\Validation\Validator::isEmpty($errors)) {
            json_response(['success' => false, 'errors' => $errors], 422);
        }

        try {
            $user = $this->service->getUserPasswordHash($userId);
            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                json_response(['success' => false, 'errors' => ['current_password' => 'Current password is incorrect']], 401);
            }
            $new_hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $this->service->updatePassword($userId, $new_hash);
            send_notification($this->service->getDb(), $user['customer_id'], 'security', 'Password Changed', 'Your account password was changed successfully. If you did not make this change, please contact support immediately.');
            json_response(['success' => true, 'message' => 'Password changed successfully']);
        } catch (PDOException $e) {
            json_response(['success' => false, 'message' => 'Failed to change password'], 500);
        }
    }

    public function completeProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        if (!$this->security->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            json_response(['success' => false, 'message' => 'Invalid security token'], 403);
        }

        $userId = $_SESSION['user_id'];
        $customer_id_str = $this->service->getCustomerId($userId);

        $data = [
            'dob' => $this->security->cleanInput($_POST['dob'] ?? ''), 'gender' => $this->security->cleanInput($_POST['gender'] ?? ''),
            'father_name' => $this->security->cleanInput($_POST['father_name'] ?? ''), 'mother_name' => $this->security->cleanInput($_POST['mother_name'] ?? ''),
            'marital_status' => $this->security->cleanInput($_POST['marital_status'] ?? ''), 'nationality' => $this->security->cleanInput($_POST['nationality'] ?? ''),
            'occupation' => $this->security->cleanInput($_POST['occupation'] ?? ''), 'annual_income' => $this->security->cleanInput($_POST['annual_income'] ?? ''),
            'source_of_income' => $this->security->cleanInput($_POST['source_of_income'] ?? ''), 'street' => $this->security->cleanInput($_POST['street'] ?? ''),
            'city' => $this->security->cleanInput($_POST['city'] ?? ''), 'state' => $this->security->cleanInput($_POST['state'] ?? ''),
            'pincode' => $this->security->cleanInput($_POST['pincode'] ?? ''), 'id_type' => $this->security->cleanInput($_POST['id_type'] ?? ''),
            'id_number' => $this->security->cleanInput($_POST['id_number'] ?? ''), 'account_type' => $this->security->cleanInput($_POST['account_type'] ?? ''),
            'initial_deposit' => floatval($_POST['initial_deposit'] ?? 0), 'txn_pin' => $_POST['txn_pin'] ?? '',
        ];

        $existing_user = $this->service->getUserFiles($userId);
        $data['has_profile_pic'] = !\App\Shared\Core\Validation\Validator::isEmpty($existing_user['profile_picture']);
        $data['has_id_front'] = !\App\Shared\Core\Validation\Validator::isEmpty($existing_user['id_front_path']);
        $data['has_id_back'] = !\App\Shared\Core\Validation\Validator::isEmpty($existing_user['id_back_path']);

        $errors = $this->service->validateKyc($data, $_FILES);
        if (!\App\Shared\Core\Validation\Validator::isEmpty($errors)) {
            json_response(['success' => false, 'errors' => $errors], 422);
        }

        try {
            $paths = $this->service->processKycUploads($_FILES, $userId, $existing_user);
        } catch (Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 500);
        }

        try {
            $pdo = $this->service->getDb();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_ef776789'));
            $stmt->execute([$paths['profile_pic_path'], $data['dob'], $data['gender'], $data['father_name'], $data['mother_name'], $data['marital_status'], $data['nationality'], $data['occupation'], $data['annual_income'], $data['source_of_income'], $data['street'], $data['city'], $data['state'], $data['pincode'], $data['id_type'], $data['id_number'], $paths['id_front_path'], $paths['id_back_path'], $userId]);

            $txn_pin_hash = password_hash($data['txn_pin'], PASSWORD_BCRYPT);
            $existing_account = $this->service->getExistingAccountByCustomerId($customer_id_str);

            if ($existing_account) {
                $this->service->updateAccountResubmission($data['account_type'], $data['initial_deposit'], $txn_pin_hash, $customer_id_str);
            } else {
                $this->service->insertPendingAccount($customer_id_str, 'PENDING_' . $userId, $data['account_type'], $data['initial_deposit'], $txn_pin_hash);
            }

            $year = date('Y');
            $next_num = $this->service->getNextKycApplicationNo($year) + 1;
            $application_no = 'KYC' . $year . str_pad($next_num, 7, '0', STR_PAD_LEFT);
            $existing_app = $this->service->getKycApplication($customer_id_str);

            if ($existing_app) {
                $new_count = $existing_app['submission_count'] + 1;
                $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_371058c0'));
                $stmt->execute([$new_count, $data['dob'], $data['gender'], $data['father_name'], $data['occupation'], $data['annual_income'], $data['street'], $data['city'], $data['state'], $data['pincode'], $data['id_type'], $data['id_number'], $paths['id_front_path'], $paths['id_back_path'], $data['account_type'], $data['initial_deposit'], $customer_id_str]);
            } else {
                $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_e07f7e3c'));
                $stmt->execute([$customer_id_str, $application_no, $data['dob'], $data['gender'], $data['father_name'], $data['occupation'], $data['annual_income'], $data['street'], $data['city'], $data['state'], $data['pincode'], $data['id_type'], $data['id_number'], $paths['id_front_path'], $paths['id_back_path'], $data['account_type'], $data['initial_deposit']]);
            }
            $pdo->commit();
            $_SESSION['user_status'] = 'under_review';
            send_under_review_email($_SESSION['user_email'], $_SESSION['user_name']);
            send_notification($pdo, $customer_id_str, 'system', 'Application Submitted for Review', 'Your KYC profile has been successfully submitted. Our team will review your documents and notify you within 24 hours.');
            json_response(['success'  => true, 'message'  => 'Your profile has been submitted for review. We will notify you once it is processed.', 'redirect' => '/views/user/dashboard.html']);
        } catch (\PDOException $e) {
            if (\App\Shared\Core\Validation\Validator::isSet($pdo)) {
                $pdo->rollBack();
            }
            if (\App\Shared\Core\Validation\Validator::isSet($paths['front_destination']) && file_exists($paths['front_destination'])) {
                unlink($paths['front_destination']);
            }
            if (\App\Shared\Core\Validation\Validator::isSet($paths['back_destination']) && file_exists($paths['back_destination'])) {
                unlink($paths['back_destination']);
            }
            \App\Shared\Core\Logging\Logger::getInstance()->error('KYC DB Error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()], 500);
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

        $userId = $_SESSION['user_id'];
        try {
            $user = $this->service->getUserDashboardData($userId);
            if (!$user) {
                destroy_session();
                json_response(['success' => false, 'message' => 'User not found', 'redirect' => '/views/auth/login.html'], 404);
            }
            $_SESSION['user_status'] = $user['status'];
            $account = $this->service->getActiveAccountByCustomerId($user['customer_id']);
            $recent_transactions = [];
            if ($account && $account['status'] === 'active') {
                $recent_transactions = $this->service->getRecentTransactions($account['id']);
                foreach ($recent_transactions as &$txn) {
                    if ($txn['type'] === 'transfer') {
                        $txn['direction'] = 'debit';
                        $txn_data = $this->service->getTransactionToAccountId($txn['txn_id']);
                        $txn['direction'] = ($txn_data && $txn_data['to_account_id'] == $account['id']) ? 'credit' : 'debit';
                    } elseif (in_array($txn['type'], ['deposit', 'interest_credit'])) {
                        $txn['direction'] = 'credit';
                    } else {
                        $txn['direction'] = 'debit';
                    }
                }
                unset($txn);
            }
            $monthly_stats = ['income_total' => 0, 'expense_total' => 0, 'month_label' => date('F Y')];
            if ($account && $account['status'] === 'active') {
                $monthly_stats['income_total'] = (float)$this->service->getMonthlyIncome($account['id']);
                $monthly_stats['expense_total'] = (float)$this->service->getMonthlyExpenses($account['id']);
            }
            if (!\App\Shared\Core\Validation\Validator::isSet($_SESSION['user_customer_id'])) {
                $_SESSION['user_customer_id'] = $this->service->getCustomerId($userId);
            }
            $customer_id = $_SESSION['user_customer_id'];
            $notification_count = (int)$this->service->getUnreadNotificationCount($customer_id);
            $greeting = get_greeting();
            $avatar_url = !\App\Shared\Core\Validation\Validator::isEmpty($user['profile_picture']) ? '../../../' . $user['profile_picture'] : null;

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
                $account = $this->service->getActiveAccountByCustomerId($user['customer_id']);
                json_response(['success' => true, 'user' => $user, 'account' => $account]);
            } catch (PDOException $e) {
                json_response(['success' => false, 'message' => 'Failed to load profile'], 500);
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->security->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                json_response(['success' => false, 'message' => 'Invalid security token'], 403);
            }
            $fullName = $this->security->cleanInput($_POST['full_name'] ?? '');
            $phone = $this->security->cleanInput($_POST['phone'] ?? '');
            $email = $this->security->cleanInput($_POST['email'] ?? '');
            $errors = [];
            if (\App\Shared\Core\Validation\Validator::isEmpty($fullName) || \App\Shared\Core\Validation\Validator::length($fullName) < 2 || \App\Shared\Core\Validation\Validator::length($fullName) > 100) {
                $errors['full_name'] = 'Full name must be between 2 and 100 characters';
            }
            if (!\App\Shared\Core\Validation\Validator::isEmpty($phone) && !validate_phone($phone)) {
                $errors['phone'] = 'Please enter a valid 10-digit Indian mobile number';
            }
            if (!\App\Shared\Core\Validation\Validator::isEmpty($email) && !\App\Shared\Core\Validation\Validator::filterVar($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address';
            }
            if (!\App\Shared\Core\Validation\Validator::isEmpty($email) && $email !== $_SESSION['user_email'] && $this->service->checkEmailExists($email, $userId)) {
                $errors['email'] = 'This email is already in use by another account';
            }
            if (!\App\Shared\Core\Validation\Validator::isEmpty($phone) && $this->service->checkPhoneExists($phone, $userId)) {
                $errors['phone'] = 'This phone number is already in use by another account';
            }
            if (!\App\Shared\Core\Validation\Validator::isEmpty($errors)) {
                json_response(['success' => false, 'errors' => $errors], 422);
            }
            try {
                $this->service->updateProfile($userId, $fullName, $phone, $email);
                $_SESSION['user_name'] = $fullName;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_phone'] = $phone;
                json_response(['success' => true, 'message' => 'Profile updated successfully']);
            } catch (PDOException $e) {
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
                if (in_array($txn['type'], ['deposit', 'interest_credit']) || ($txn['type'] === 'transfer' && $txn['to_account_id'] == $account['id'])) {
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
            $search = $this->security->cleanInput($_GET['search'] ?? '');
            $type = $this->security->cleanInput($_GET['type'] ?? 'all');
            $status = $this->security->cleanInput($_GET['status'] ?? 'all');
            $fromDate = $this->security->cleanInput($_GET['from_date'] ?? '');
            $toDate = $this->security->cleanInput($_GET['to_date'] ?? '');
            $page = max(1, intval($_GET['page'] ?? 1));
            $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

            $where_clauses = ["(t.from_account_id = ? OR t.to_account_id = ?)"];
            $params = [$account_id, $account_id];
            if ($type !== 'all' && in_array($type, ['deposit', 'withdrawal', 'transfer', 'interest_credit'])) {
                $where_clauses[] = "t.type = ?";
                $params[] = $type;
            }
            if ($status !== 'all' && in_array($status, ['completed', 'pending', 'failed', 'reversed'])) {
                $where_clauses[] = "t.status = ?";
                $params[] = $status;
            }
            if (!\App\Shared\Core\Validation\Validator::isEmpty($fromDate)) {
                $where_clauses[] = "DATE(t.created_at) >= ?";
                $params[] = $fromDate;
            }
            if (!\App\Shared\Core\Validation\Validator::isEmpty($toDate)) {
                $where_clauses[] = "DATE(t.created_at) <= ?";
                $params[] = $toDate;
            }
            if (!\App\Shared\Core\Validation\Validator::isEmpty($search)) {
                $where_clauses[] = "(t.txn_id LIKE ? OR t.description LIKE ? OR t.reference_note LIKE ?)";
                $search_param = "%{$search}%";
                $params[] = $search_param;
                $params[] = $search_param;
                $params[] = $search_param;
            }

            $where_sql = implode(' AND ', $where_clauses);
            $total = (int)$this->service->getTransactionsCount($where_sql, $params);
            $pages = max(1, ceil($total / $per_page));
            $offset = ($page - 1) * $per_page;
            $transactions = $this->service->getTransactionsList($where_sql, $params, $per_page, $offset);

            foreach ($transactions as &$txn) {
                if (in_array($txn['type'], ['deposit', 'interest_credit'])) {
                    $txn['direction'] = 'credit';
                } elseif ($txn['type'] === 'withdrawal') {
                    $txn['direction'] = 'debit';
                } elseif ($txn['type'] === 'transfer') {
                    $txn['direction'] = ($txn['to_account_id'] == $account_id) ? 'credit' : 'debit';
                    if ($txn['to_account_id'] == $account_id && $txn['dest_balance_after'] !== null) {
                        $txn['balance_after'] = $txn['dest_balance_after'];
                    }
                }
            }
            unset($txn);
            json_response(['success' => true, 'transactions' => $transactions, 'total' => $total, 'pages' => $pages, 'current_page' => $page, 'per_page' => $per_page]);
        } catch (PDOException $e) {
            json_response(['success' => false, 'message' => 'Failed to load transactions'], 500);
        }
    }

    public function transfer()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        if (!$this->security->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            json_response(['success' => false, 'message' => 'Invalid security token'], 403);
        }
        if ($_SESSION['user_status'] !== 'active') {
            json_response(['success' => false, 'message' => 'Your account is not active. Transfers are not allowed.'], 403);
        }

        $userId = $_SESSION['user_id'];
        $to_account_no = $this->security->cleanInput($_POST['to_account_no'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        $description = $this->security->cleanInput($_POST['description'] ?? '');
        $transactionPin = $_POST['txn_pin'] ?? '';
        $errors = [];
        if (\App\Shared\Core\Validation\Validator::isEmpty($to_account_no)) {
            $errors['to_account_no'] = 'Recipient account number is required';
        }
        if ($amount <= 0) {
            $errors['amount'] = 'Amount must be greater than zero';
        } elseif ($amount != round($amount, 2)) {
            $errors['amount'] = 'Amount can have at most 2 decimal places';
        }
        if (\App\Shared\Core\Validation\Validator::length($description) > 100) {
            $errors['description'] = 'Description must not exceed 100 characters';
        }
        if (\App\Shared\Core\Validation\Validator::isEmpty($transactionPin) || !\App\Shared\Core\Validation\Validator::pregMatch('/^\d{4}$/', $transactionPin)) {
            $errors['txn_pin'] = 'Please enter your 4-digit transaction PIN';
        }
        if (!\App\Shared\Core\Validation\Validator::isEmpty($errors)) {
            json_response(['success' => false, 'errors' => $errors], 422);
        }

        $pdo = $this->service->getDb();
        if (!validate_pin($pdo, $userId, $transactionPin)) {
            json_response(['success' => false, 'message' => 'Invalid transaction PIN'], 401);
        }

        try {
            $pdo->beginTransaction();
            $account = $this->service->getActiveAccount($userId);
            if (!$account) {
                $pdo->rollBack();
                json_response(['success' => false, 'message' => 'Source account not found or inactive'], 404);
            }
            $source_id = $account['id'];
            $dest_id = $this->service->getAccountIdByNumber($to_account_no);
            if (!$dest_id) {
                $pdo->rollBack();
                json_response(['success' => false, 'message' => 'Destination account not found or inactive'], 404);
            }
            if ($source_id === $dest_id) {
                $pdo->rollBack();
                json_response(['success' => false, 'message' => 'Cannot transfer to your own account'], 422);
            }

            $first_id = min($source_id, $dest_id);
            $second_id = max($source_id, $dest_id);
            $first = $this->service->getAccountForUpdate($first_id);
            $second = $this->service->getAccountForUpdate($second_id);
            $source = ($first['id'] == $source_id) ? $first : $second;
            $dest = ($first['id'] == $dest_id) ? $first : $second;
            if (!$source || !$dest) {
                $pdo->rollBack();
                json_response(['success' => false, 'message' => 'Account error during locking'], 500);
            }
            $dest['dest_user_id'] = $dest['customer_id'];
            if ($source['balance'] < $amount) {
                $pdo->rollBack();
                json_response(['success' => false, 'message' => 'Insufficient balance'], 422);
            }
            $min_transfer = get_setting($pdo, 'min_transfer_amount', 10);
            if ($amount < $min_transfer) {
                $pdo->rollBack();
                json_response(['success' => false, 'message' => 'Minimum transfer amount is ' . format_currency($min_transfer)], 422);
            }

            $today_total = (float)$this->service->fetchTodayTransferTotal($source['id']);
            $daily_limit = $source['daily_limit'];
            if (($today_total + $amount) > $daily_limit) {
                $pdo->rollBack();
                $remaining = max(0, $daily_limit - $today_total);
                json_response(['success' => false, 'message' => "Daily transfer limit exceeded. Remaining limit: " . format_currency($remaining)], 422);
            }

            $new_source_balance = $source['balance'] - $amount;
            $this->service->updateAccountBalance($source['id'], -$amount);
            $new_dest_balance = $dest['balance'] + $amount;
            $this->service->updateAccountBalance($dest['id'], $amount);

            $transactionId = generate_transaction_id();
            $this->service->insertTransaction($transactionId, $source['id'], $dest['id'], $amount, $new_source_balance, $new_dest_balance, $description);

            $safe_dest_name = html_encode($dest['full_name']);
            $safe_source_name = html_encode($source['full_name']);

            send_notification($pdo, $source['owner_user_id'], 'transaction', 'Transfer Successful', "You transferred " . format_currency($amount) . " to {$safe_dest_name} ({$to_account_no}). Transaction ID: {$transactionId}");
            send_notification($pdo, $dest['owner_user_id'], 'transaction', 'Money Received', "You received " . format_currency($amount) . " from {$safe_source_name} ({$source['account_no']}). Transaction ID: {$transactionId}");
            $this->security->writeAuditLog($pdo, 'fund_transfer', $transactionId, 'transaction', $userId, 'user', "Transfer of " . format_currency($amount) . " from {$source['account_no']} to {$to_account_no}");

            $pdo->commit();
            $_SESSION['user_balance'] = $new_source_balance;
            json_response(['success' => true, 'message' => 'Transfer successful', 'transaction_id' => $transactionId, 'amount' => $amount, 'to_name' => $safe_dest_name, 'to_account' => $to_account_no, 'new_balance' => $new_source_balance, 'redirect' => '/views/user/receipt.html?id=' . $transactionId]);
        } catch (PDOException $e) {
            if (\App\Shared\Core\Validation\Validator::isSet($pdo)) {
                $pdo->rollBack();
            } json_response(['success' => false, 'message' => 'Transfer failed. Please try again.'], 500);
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
