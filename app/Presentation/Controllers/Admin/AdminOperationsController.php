<?php

namespace App\Presentation\Controllers\Admin;

use App\Application\Services\AdminOperationsService;
use App\Shared\Core\Exceptions\ApiException;
use PDOException;

class AdminOperationsController
{
    protected AdminOperationsService $service;

    public function __construct(AdminOperationsService $service)
    {
        $this->service = $service;
    }

    public function auditLog(): array
    {
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
        return $data;
    }

    public function dashboard(): array
    {
        $admin_id = $_SESSION['admin_id'];
        $data = $this->service->getDashboardData($admin_id);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return [
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
        ];
    }

    public function deletedUsers(): array
    {
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
        return $data;
    }

    public function exportUsers()
    {
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
        if (defined('FINZO_TESTING') && FINZO_TESTING) {
            throw new \Exception('Exit called after export');
        }
        exit();
    }

    public function interest(): array
    {
        $admin_id = $_SESSION['admin_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = $this->service->getInterestData();
            $data['success'] = true;
            return $data;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            global $pdo;
            check_admin_permission($pdo, $admin_id, 'change_interest_rate');

            $new_rate       = floatval($_POST['new_rate'] ?? 0);
            $apply_from     = clean_input($_POST['apply_from'] ?? 'Immediately');
            $custom_date    = clean_input($_POST['custom_date'] ?? '');

            $result = $this->service->updateInterestRate($new_rate, $apply_from, $custom_date, $admin_id);
            if (!$result['success']) {
                if (isset($result['errors'])) {
                    throw new ApiException('Validation failed', 422, ['errors' => $result['errors']]);
                }
                throw new ApiException($result['message'], $result['code'] ?? 500);
            }

            return $result;
        }

        throw new ApiException('Method not allowed', 405);
    }

    public function lockedAccounts(): array
    {
        $filters = [
            'tab'    => clean_input($_GET['tab'] ?? 'locked'),
            'search' => clean_input($_GET['search'] ?? ''),
            'status' => clean_input($_GET['status'] ?? '')
        ];
        $page     = max(1, intval($_GET['page'] ?? 1));
        $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

        $data = $this->service->getLockedAccounts($filters, $page, $per_page);
        $data['success'] = true;
        return $data;
    }

    public function lockedAccountDetail(): array
    {
        $user_id = intval($_GET['id'] ?? 0);
        if (!$user_id) {
            throw new ApiException('User ID is required', 422);
        }

        $result = $this->service->getLockedAccountDetail($user_id);
        if (!$result['success']) {
            throw new ApiException($result['message'], $result['code'] ?? 500);
        }
        return $result;
    }

    public function pendingApprovals(): array
    {
        $filters = [
            'status' => clean_input($_GET['status'] ?? 'all'),
            'search' => clean_input($_GET['search'] ?? '')
        ];
        $page     = max(1, intval($_GET['page'] ?? 1));
        $per_page = min(50, max(10, intval($_GET['per_page'] ?? 10)));

        $data = $this->service->getPendingApprovals($filters, $page, $per_page);
        return [
            'success' => true,
            'data'    => $data
        ];
    }

    public function recoverUser(): array
    {
        $user_id = intval($_POST['user_id'] ?? 0);
        if (!$user_id) {
            throw new ApiException('User ID is required', 422);
        }

        $admin_id = $_SESSION['admin_id'];
        $result = $this->service->recoverUser($user_id, $admin_id);
        if (!$result['success']) {
            throw new ApiException($result['message'], $result['code'] ?? 500);
        }
        return $result;
    }

    public function reviewApplication(): array
    {
        $admin_id = $_SESSION['admin_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $user_id = intval($_GET['user_id'] ?? $_GET['id'] ?? 0);
            if (!$user_id) {
                throw new ApiException('User ID is required', 422);
            }

            $result = $this->service->getApplicationDetails($user_id);
            if (!$result['success']) {
                throw new ApiException($result['message'], $result['code'] ?? 500);
            }
            return $result;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = intval($_POST['user_id'] ?? 0);
            $action  = clean_input($_POST['action'] ?? '');
            $reason  = clean_input($_POST['reason'] ?? '');
            $notes   = clean_input($_POST['notes'] ?? '');

            if (!$user_id || empty($action)) {
                throw new ApiException('User ID and action are required', 422);
            }

            $result = $this->service->processApplication($user_id, $action, $reason, $notes, $admin_id);
            if (!$result['success']) {
                throw new ApiException($result['message'], $result['code'] ?? 500);
            }
            return $result;
        }

        throw new ApiException('Method not allowed', 405);
    }

    public function viewKycDocument(): void
    {
        $admin_id = $_SESSION['admin_id'] ?? null;
        if (!$admin_id) {
            http_response_code(401);
            echo "Unauthorized";
            exit;
        }

        $path = $_GET['path'] ?? '';

        // Basic security check: prevent directory traversal and ensure it's pointing to storage/kyc-documents
        if (empty($path) || strpos($path, '..') !== false || !str_starts_with($path, 'storage/kyc-documents/')) {
            http_response_code(403);
            echo "Forbidden";
            exit;
        }

        $absolute_path = dirname(__DIR__, 3) . '/' . $path;

        if (!file_exists($absolute_path)) {
            http_response_code(404);
            echo "Document not found";
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $absolute_path);

        header("Content-Type: " . $mime_type);
        header("Content-Length: " . filesize($absolute_path));

        // Prevent caching of sensitive documents
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        readfile($absolute_path);
        exit;
    }

    public function suspendedUsers(): array
    {
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
        return $data;
    }

    public function transactions()
    {
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
            if (defined('FINZO_TESTING') && FINZO_TESTING) {
                throw new \Exception('Exit called after export');
            }
            exit;
        }

        $data = $this->service->getTransactions($filters, $page, $per_page, false);
        $data['success'] = true;
        return $data;
    }

    public function users(): array
    {
        $filters = [
            'search' => clean_input($_GET['search'] ?? ''),
            'status' => clean_input($_GET['status'] ?? 'all')
        ];
        $page     = max(1, intval($_GET['page'] ?? 1));
        $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

        $data = $this->service->getUsersList($filters, $page, $per_page);
        $data['success'] = true;
        return $data;
    }

    public function suspendedUserDetail(): array
    {
        $user_id = intval($_GET['id'] ?? 0);
        if (!$user_id) {
            throw new ApiException('User ID is required', 422);
        }

        $data = $this->service->getSuspendedUserDetail($user_id);
        if (!$data['success']) {
            throw new ApiException($data['message'], 404);
        }
        return $data;
    }

    public function unlockAccount(): array
    {
        $user_id  = intval($_POST['user_id'] ?? 0);
        $admin_id = $_SESSION['admin_id'];
        if (!$user_id) {
            throw new ApiException('User ID is required', 422);
        }

        $data = $this->service->unlockAccount($user_id, $admin_id);
        if (!$data['success']) {
            throw new ApiException($data['message'], $data['code'] ?? 500);
        }
        return $data;
    }

    public function userAction(): array
    {
        $user_id = intval($_POST['user_id'] ?? 0);
        $action  = clean_input($_POST['action'] ?? '');
        $reason  = clean_input($_POST['reason'] ?? '');
        $password = $_POST['password'] ?? '';
        $balance_option = clean_input($_POST['balance_option'] ?? 'full');
        $admin_id = $_SESSION['admin_id'];

        if (!$user_id || empty($action)) {
            throw new ApiException('User ID and action are required', 422);
        }

        $data = $this->service->processUserAction($user_id, $action, $reason, $password, $balance_option, $admin_id);
        if (!$data['success']) {
            throw new ApiException($data['message'], $data['code'] ?? 400);
        }
        return $data;
    }

    public function userDetail(): array
    {
        $user_id = intval($_GET['user_id'] ?? $_GET['id'] ?? 0);
        if (!$user_id) {
            throw new ApiException('User ID is required', 422);
        }

        $data = $this->service->getUserDetail($user_id);
        if (!$data['success']) {
            throw new ApiException($data['message'], 404);
        }
        return $data;
    }
}
