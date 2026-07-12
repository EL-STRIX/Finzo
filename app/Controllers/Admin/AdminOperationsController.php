<?php
namespace App\Controllers\Admin;

use App\Services\AdminOperationsService;
use PDOException;

class AdminOperationsController {
    protected AdminOperationsService $service;

    public function __construct(AdminOperationsService $service) {
        $this->service = $service;
    }

    public function auditLog() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        try {
            $filters = [
                'search'      => clean_input($_GET['search'] ?? ''),
                'action_type' => clean_input($_GET['action_type'] ?? 'all'),
                'actor_role'  => clean_input($_GET['actor_role'] ?? 'all'),
                'from_date'   => clean_input($_GET['from_date'] ?? ''),
                'to_date'     => clean_input($_GET['to_date'] ?? '')
            ];
            
            $page     = max(1, intval($_GET['page'] ?? 1));
            $per_page = min(100, max(10, intval($_GET['per_page'] ?? 25)));

            $data = $this->service->getAuditLog($filters, $page, $per_page);
            $data['success'] = true;
            json_response($data);
        } catch (PDOException $e) {
            error_log("Audit log API error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load audit log'], 500);
        }
    }

    public function dashboard() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $admin_id = $_SESSION['admin_id'];
            $data = $this->service->getDashboardData($admin_id);

            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }

            json_response([
                'success' => true,
                'csrf_token' => $_SESSION['csrf_token'],
                'admin' => [
                    'id' => $admin_id,
                    'full_name' => $data['admin']['full_name'],
                    'email' => $data['admin']['email'],
                    'profile_photo' => $data['admin']['profile_photo'],
                    'role' => $data['admin']['admin_role']
                ],
                'stats' => $data['stats'],
                'recent_users' => $data['recent_users'],
                'monthly_chart_data' => $data['monthly_chart_data']
            ]);
        } catch (PDOException $e) {
            error_log("Admin dashboard error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load dashboard data'], 500);
        }
    }

    public function deletedUsers() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $filters = [
                'search'       => clean_input($_GET['search'] ?? ''),
                'date_from'    => clean_input($_GET['date_from'] ?? ''),
                'date_to'      => clean_input($_GET['date_to'] ?? ''),
                'account_type' => clean_input($_GET['account_type'] ?? '')
            ];
            $page     = max(1, intval($_GET['page'] ?? 1));
            $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

            $data = $this->service->getDeletedUsers($filters, $page, $per_page);
            $data['success'] = true;
            json_response($data);
        } catch (PDOException $e) {
            error_log("Deleted users API error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load deleted users'], 500);
        }
    }

    public function exportUsers() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $filters = [
                'search' => clean_input($_GET['search'] ?? ''),
                'status' => clean_input($_GET['status'] ?? 'all')
            ];

            $users = $this->service->exportUsers($filters);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=Finzo_Users_Export_' . date('Y-m-d') . '.csv');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Customer ID', 'Full Name', 'Email', 'Phone', 'Status', 'Joined Date', 'Last Active']);
            
            foreach ($users as $row) {
                // Prevent CSV Formula Injection
                $safe_name = $row['full_name'];
                if (preg_match('/^[=\-+@\t\r]/', $safe_name)) {
                    $safe_name = "'" . $safe_name;
                }

                fputcsv($output, [
                    $row['customer_id'] ?: 'Pending',
                    $safe_name,
                    $row['email'],
                    $row['phone'],
                    ucfirst($row['status']),
                    $row['created_at'],
                    $row['last_login_at'] ?: 'Never'
                ]);
            }
            
            fclose($output);
            exit();
        } catch (PDOException $e) {
            error_log("Admin users export API error: " . $e->getMessage());
            die("Failed to generate export file.");
        }
    }

    public function interest() {
        $admin_id = $_SESSION['admin_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                $data = $this->service->getInterestData();
                $data['success'] = true;
                json_response($data);
            } catch (PDOException $e) {
                error_log("Interest rate GET error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'Failed to load interest rate data'], 500);
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            global $pdo;
            check_admin_permission($pdo, $admin_id, 'change_interest_rate');

            $new_rate       = floatval($_POST['new_rate'] ?? 0);
            $apply_from     = clean_input($_POST['apply_from'] ?? 'Immediately');
            $custom_date    = clean_input($_POST['custom_date'] ?? '');

            try {
                $result = $this->service->updateInterestRate($new_rate, $apply_from, $custom_date, $admin_id);
                if (!$result['success']) {
                    if (isset($result['errors'])) {
                        json_response(['success' => false, 'errors' => $result['errors']], 422);
                    }
                    json_response(['success' => false, 'message' => $result['message']], $result['code'] ?? 500);
                }

                json_response($result);
            } catch (PDOException $e) {
                error_log("Interest rate update error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'Failed to update interest rate'], 500);
            }
        }

        json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    public function lockedAccounts() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $filters = [
                'tab'    => clean_input($_GET['tab'] ?? 'locked'),
                'search' => clean_input($_GET['search'] ?? ''),
                'status' => clean_input($_GET['status'] ?? '')
            ];
            $page     = max(1, intval($_GET['page'] ?? 1));
            $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

            $data = $this->service->getLockedAccounts($filters, $page, $per_page);
            $data['success'] = true;
            json_response($data);
        } catch (PDOException $e) {
            error_log("Locked accounts API error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load locked accounts'], 500);
        }
    }

    public function lockedAccountDetail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $user_id = intval($_GET['id'] ?? 0);
        if (!$user_id) {
            json_response(['success' => false, 'message' => 'User ID is required'], 422);
        }

        try {
            $result = $this->service->getLockedAccountDetail($user_id);
            if (!$result['success']) {
                json_response(['success' => false, 'message' => $result['message']], $result['code']);
            }
            json_response($result);
        } catch (PDOException $e) {
            error_log("Admin locked account detail error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load details: ' . $e->getMessage()], 500);
        }
    }

    public function pendingApprovals() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $filters = [
                'status' => clean_input($_GET['status'] ?? 'all'),
                'search' => clean_input($_GET['search'] ?? '')
            ];
            $page     = max(1, intval($_GET['page'] ?? 1));
            $per_page = min(50, max(10, intval($_GET['per_page'] ?? 10)));

            $data = $this->service->getPendingApprovals($filters, $page, $per_page);
            json_response([
                'success' => true,
                'data'    => $data
            ]);
        } catch (PDOException $e) {
            error_log("Pending approvals API error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load pending approvals'], 500);
        }
    }

    public function recoverUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $user_id = intval($_POST['user_id'] ?? 0);
        if (!$user_id) {
            json_response(['success' => false, 'message' => 'User ID is required'], 422);
        }

        try {
            $admin_id = $_SESSION['admin_id'];
            $result = $this->service->recoverUser($user_id, $admin_id);
            if (!$result['success']) {
                json_response(['success' => false, 'message' => $result['message']], $result['code'] ?? 500);
            }
            json_response($result);
        } catch (PDOException $e) {
            error_log("Recover user error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to recover user'], 500);
        }
    }

    public function reviewApplication() {
        $admin_id = $_SESSION['admin_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $user_id = intval($_GET['user_id'] ?? $_GET['id'] ?? 0);
            if (!$user_id) {
                json_response(['success' => false, 'message' => 'User ID is required'], 422);
            }

            try {
                $result = $this->service->getApplicationDetails($user_id);
                if (!$result['success']) {
                    json_response(['success' => false, 'message' => $result['message']], $result['code'] ?? 500);
                }
                json_response($result);
            } catch (PDOException $e) {
                error_log("Review application GET error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'Failed to load application'], 500);
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = intval($_POST['user_id'] ?? 0);
            $action  = clean_input($_POST['action'] ?? '');
            $reason  = clean_input($_POST['reason'] ?? '');
            $notes   = clean_input($_POST['notes'] ?? '');

            if (!$user_id || empty($action)) {
                json_response(['success' => false, 'message' => 'User ID and action are required'], 422);
            }

            try {
                $result = $this->service->processApplication($user_id, $action, $reason, $notes, $admin_id);
                if (!$result['success']) {
                    json_response(['success' => false, 'message' => $result['message']], $result['code'] ?? 500);
                }
                json_response($result);
            } catch (PDOException $e) {
                error_log("Review application POST error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'Action failed. Please try again.'], 500);
            }
        }

        json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    public function suspendedUsers() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $filters = [
                'search'     => clean_input($_GET['search'] ?? ''),
                'reason'     => clean_input($_GET['reason'] ?? ''),
                'start_date' => clean_input($_GET['start_date'] ?? ''),
                'end_date'   => clean_input($_GET['end_date'] ?? '')
            ];
            $page     = max(1, intval($_GET['page'] ?? 1));
            $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

            $data = $this->service->getSuspendedUsers($filters, $page, $per_page);
            $data['success'] = true;
            json_response($data);
        } catch (PDOException $e) {
            error_log("Suspended users API error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load suspended users'], 500);
        }
    }

    public function transactions() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $filters = [
                'search'    => clean_input($_GET['search'] ?? ''),
                'type'      => clean_input($_GET['type'] ?? 'all'),
                'status'    => clean_input($_GET['status'] ?? 'all'),
                'from_date' => clean_input($_GET['from_date'] ?? ''),
                'to_date'   => clean_input($_GET['to_date'] ?? '')
            ];
            $page     = max(1, intval($_GET['page'] ?? 1));
            $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));
            $export   = clean_input($_GET['export'] ?? '');

            if ($export === 'csv') {
                $rows = $this->service->getTransactions($filters, $page, $per_page, true);

                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="finzo_transactions_' . date('Y-m-d') . '.csv"');

                $output = fopen('php://output', 'w');
                if (!empty($rows)) {
                    fputcsv($output, array_keys($rows[0]));
                    foreach ($rows as $row) {
                        fputcsv($output, $row);
                    }
                }
                fclose($output);
                exit;
            }

            $data = $this->service->getTransactions($filters, $page, $per_page, false);
            $data['success'] = true;
            json_response($data);
        } catch (PDOException $e) {
            error_log("Admin transactions API error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load transactions'], 500);
        }
    }

    public function users() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $filters = [
                'search' => clean_input($_GET['search'] ?? ''),
                'status' => clean_input($_GET['status'] ?? 'all')
            ];
            $page     = max(1, intval($_GET['page'] ?? 1));
            $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

            $data = $this->service->getUsersList($filters, $page, $per_page);
            $data['success'] = true;
            json_response($data);
        } catch (PDOException $e) {
            error_log("Admin users API error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load users'], 500);
        }
    }
    public function suspendedUserDetail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $user_id = intval($_GET['id'] ?? 0);
        if (!$user_id) json_response(['success' => false, 'message' => 'User ID is required'], 422);

        try {
            $data = $this->service->getSuspendedUserDetail($user_id);
            if (!$data['success']) {
                json_response(['success' => false, 'message' => $data['message']], 404);
            }
            json_response($data);
        } catch (PDOException $e) {
            error_log("Admin suspended user detail error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load suspended user details'], 500);
        }
    }

    public function unlockAccount() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $user_id  = intval($_POST['user_id'] ?? 0);
        $admin_id = $_SESSION['admin_id'];
        if (!$user_id) json_response(['success' => false, 'message' => 'User ID is required'], 422);

        try {
            $data = $this->service->unlockAccount($user_id, $admin_id);
            if (!$data['success']) {
                json_response(['success' => false, 'message' => $data['message']], $data['code'] ?? 500);
            }
            json_response($data);
        } catch (PDOException $e) {
            error_log("Unlock account error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to unlock account'], 500);
        }
    }

    public function userAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $user_id = intval($_POST['user_id'] ?? 0);
        $action  = clean_input($_POST['action'] ?? '');
        $reason  = clean_input($_POST['reason'] ?? '');
        $password = $_POST['password'] ?? '';
        $balance_option = clean_input($_POST['balance_option'] ?? 'full');
        $admin_id = $_SESSION['admin_id'];

        if (!$user_id || empty($action)) {
            json_response(['success' => false, 'message' => 'User ID and action are required'], 422);
        }

        try {
            $data = $this->service->processUserAction($user_id, $action, $reason, $password, $balance_option, $admin_id);
            if (!$data['success']) {
                json_response(['success' => false, 'message' => $data['message']], $data['code'] ?? 400);
            }
            json_response($data);
        } catch (PDOException $e) {
            error_log("Admin user action error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Action failed. Please try again.'], 500);
        }
    }

    public function userDetail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $user_id = intval($_GET['user_id'] ?? $_GET['id'] ?? 0);
        if (!$user_id) json_response(['success' => false, 'message' => 'User ID is required'], 422);

        try {
            $data = $this->service->getUserDetail($user_id);
            if (!$data['success']) {
                json_response(['success' => false, 'message' => $data['message']], 404);
            }
            json_response($data);
        } catch (PDOException $e) {
            error_log("Admin user detail error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load user details'], 500);
        }
    }
}
