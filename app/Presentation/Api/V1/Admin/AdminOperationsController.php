<?php

namespace App\Presentation\Api\V1\Admin;

use App\Shared\Security\SecurityService;

use App\Application\Services\AdminOperationsService;
use App\Shared\Core\Exceptions\ApiException;
use PDOException;

class AdminOperationsController
{
    protected SecurityService $security;

    protected AdminOperationsService $service;

    public function __construct(\App\Domain\Contracts\Services\AdminOperationsServiceInterface $service, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security ?? new \App\Shared\Security\SecurityService();
        $this->service = $service;
    }

    public function auditLog(?string $id = null, ?string $extra = null): array
    {
        $filters = [
            'search'      => $this->security->cleanInput($_GET['search'] ?? ''),
            'action_type' => $this->security->cleanInput($_GET['action_type'] ?? 'all'),
            'actor_role'  => $this->security->cleanInput($_GET['actor_role'] ?? 'all'),
            'status'      => $this->security->cleanInput($_GET['status'] ?? 'all'),
            'from_date'   => $this->security->cleanInput($_GET['from_date'] ?? ''),
            'to_date'     => $this->security->cleanInput($_GET['to_date'] ?? '')
        ];

        $export = $this->security->cleanInput($_GET['export'] ?? '');
        if ($export === 'csv') {
            $generator = $this->service->exportAuditLogs($filters);

            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="finzo_audit_logs_' . date('Y-m-d') . '.csv"');
            header('X-Accel-Buffering: no');

            $output = fopen('php://output', 'w');
            $headerWritten = false;
            foreach ($generator as $row) {
                if (!$headerWritten) {
                    fputcsv($output, array_keys($row), ',', '"', "\\");
                    $headerWritten = true;
                }
                fputcsv($output, $row, ',', '"', "\\");
            }
            fclose($output);
            if (defined('FINZO_TESTING') && FINZO_TESTING) {
                throw new \Exception('Exit called after export');
            }
            exit;
        }

        $page     = max(1, intval($_GET['page'] ?? 1));
        $per_page = min(100, max(5, intval($_GET['per_page'] ?? 25)));

        $data = $this->service->getAuditLog($filters, $page, $per_page);
        $data['success'] = true;
        return $data;
    }

    public function session(?string $id = null, ?string $extra = null): array
    {
        $adminId = \App\Shared\Security\Auth::user()['id'] ?? 0;
        
        $pdo = \App\Shared\Core\Database::getInstance();
        $stmt = $pdo->prepare("SELECT id, admin_id as customer_id, full_name, profile_photo, admin_role FROM admin_users WHERE id = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'csrf_token' => $_SESSION['csrf_token'] ?? '',
            'admin' => $admin ?: \App\Shared\Security\Auth::user()
        ];
    }

    public function dashboard(?string $id = null, ?string $extra = null): array
    {
        $adminId = \App\Shared\Security\Auth::user()['id'];
        $data = $this->service->getDashboardData($adminId);



        return [
            'success' => true,
            'csrf_token' => $_SESSION['csrf_token'],
            'admin' => [
                'id' => $adminId,
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

    public function deletedUsers(?string $id = null, ?string $extra = null): array
    {
        $filters = [
            'search'       => $this->security->cleanInput($_GET['search'] ?? ''),
            'date_from'    => $this->security->cleanInput($_GET['date_from'] ?? ''),
            'date_to'      => $this->security->cleanInput($_GET['date_to'] ?? ''),
            'account_type' => $this->security->cleanInput($_GET['account_type'] ?? '')
        ];
        $page     = max(1, intval($_GET['page'] ?? 1));
        $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

        $data = $this->service->getDeletedUsers($filters, $page, $per_page);
        $data['success'] = true;
        return $data;
    }



    public function interest(?string $id = null, ?string $extra = null): array
    {
        $adminId = \App\Shared\Security\Auth::user()['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = $this->service->getInterestData();
            $data['success'] = true;
            return $data;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->security->checkAdminPermission($adminId, 'change_interest_rate');

            $new_rate       = floatval($_POST['new_rate'] ?? 0);
            $apply_from     = $this->security->cleanInput($_POST['apply_from'] ?? 'Immediately');
            $custom_date    = $this->security->cleanInput($_POST['custom_date'] ?? '');

            $result = $this->service->updateInterestRate($new_rate, $apply_from, $custom_date, $adminId);
            if (!$result['success']) {
                if (\App\Shared\Core\Validation\Validator::isSet($result['errors'])) {
                    throw new ApiException('Validation failed', 422, ['errors' => $result['errors']]);
                }
                throw new ApiException($result['message'], $result['code'] ?? 500);
            }

            return $result;
        }

        throw new ApiException('Method not allowed', 405);
    }

    public function lockedAccounts(?string $id = null, ?string $extra = null): array
    {
        $filters = [
            'tab'    => $this->security->cleanInput($_GET['tab'] ?? 'locked'),
            'search' => $this->security->cleanInput($_GET['search'] ?? ''),
            'status' => $this->security->cleanInput($_GET['status'] ?? '')
        ];
        $page     = max(1, intval($_GET['page'] ?? 1));
        $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

        $data = $this->service->getLockedAccounts($filters, $page, $per_page);
        $data['success'] = true;
        return $data;
    }

    public function lockedAccountDetail(?string $id = null, ?string $extra = null): array
    {
        $userId = intval(($id ?? $_GET['id'] ?? null) ?? 0);
        if (!$userId) {
            throw new ApiException('User ID is required', 422);
        }

        $result = $this->service->getLockedAccountDetail($userId);
        if (!$result['success']) {
            throw new ApiException($result['message'], $result['code'] ?? 500);
        }
        return $result;
    }

    public function pendingApprovals(?string $id = null, ?string $extra = null): array
    {
        $filters = [
            'status' => $this->security->cleanInput($_GET['status'] ?? 'all'),
            'search' => $this->security->cleanInput($_GET['search'] ?? '')
        ];
        $page     = max(1, intval($_GET['page'] ?? 1));
        $per_page = min(50, max(10, intval($_GET['per_page'] ?? 10)));

        $data = $this->service->getPendingApprovals($filters, $page, $per_page);
        $data['success'] = true;
        return $data;
    }

    public function recoverUser(?string $id = null, ?string $extra = null): array
    {
        $adminId = \App\Shared\Security\Auth::user()['id'];
        $userId = intval($id);
        if (!$userId) {
            throw new ApiException('User ID is required', 422);
        }

        $result = $this->service->recoverUser($userId, $adminId);
        if (!$result['success']) {
            throw new ApiException($result['message'], $result['code'] ?? 500);
        }
        return $result;
    }

    public function reviewApplication(?string $id = null, ?string $extra = null): array
    {
        $adminId = \App\Shared\Security\Auth::user()['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $userId = intval($_GET['user_id'] ?? ($id ?? $_GET['id'] ?? null) ?? 0);
            if (!$userId) {
                throw new ApiException('User ID is required', 422);
            }

            $result = $this->service->getApplicationDetails($userId);
            if (!$result['success']) {
                throw new ApiException($result['message'], $result['code'] ?? 500);
            }
            return $result;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $is_json = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
            if ($is_json) {
                $json_input = json_decode(file_get_contents('php://input'), true);
                if ($json_input !== null) {
                    $_POST = array_merge($_POST, $json_input);
                }
            }

            $userId = intval($_POST['user_id'] ?? ($id ?? 0));
            $action  = $this->security->cleanInput($_POST['action'] ?? '');
            $reason  = $this->security->cleanInput($_POST['reason'] ?? '');
            $notes   = $this->security->cleanInput($_POST['notes'] ?? '');

            if (!$userId || \App\Shared\Core\Validation\Validator::isEmpty($action)) {
                throw new ApiException('User ID and action are required', 422);
            }

            $result = $this->service->processApplication($userId, $action, $reason, $notes, $adminId);
            if (!$result['success']) {
                throw new ApiException($result['message'], $result['code'] ?? 500);
            }
            return $result;
        }

        throw new ApiException('Method not allowed', 405);
    }

    public function viewKycDocument(?string $id = null, ?string $extra = null): void
    {
        $adminId = \App\Shared\Security\Auth::user()['id'] ?? null;
        if (!$adminId) {
            http_response_code(401);
            echo "Unauthorized";
            exit;
        }

        $path = $_GET['path'] ?? '';

        // Basic security check: prevent directory traversal and ensure it's pointing to storage/kyc-documents
        if (\App\Shared\Core\Validation\Validator::isEmpty($path) || strpos($path, '..') !== false || !str_starts_with($path, 'storage/kyc-documents/')) {
            http_response_code(403);
            echo "Forbidden";
            exit;
        }

        $root = defined('FINZO_ROOT') ? FINZO_ROOT : dirname(__DIR__, 5);
        $absolute_path = $root . '/' . $path;

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

    public function suspendedUsers(?string $id = null, ?string $extra = null): array
    {
        $filters = [
            'search'     => $this->security->cleanInput($_GET['search'] ?? ''),
            'reason'     => $this->security->cleanInput($_GET['reason'] ?? ''),
            'start_date' => $this->security->cleanInput($_GET['start_date'] ?? ''),
            'end_date'   => $this->security->cleanInput($_GET['end_date'] ?? '')
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
            'search'     => $this->security->cleanInput($_GET['search'] ?? ''),
            'type'       => $this->security->cleanInput($_GET['type'] ?? 'all'),
            'status'     => $this->security->cleanInput($_GET['status'] ?? 'all'),
            'from_date'  => $this->security->cleanInput($_GET['from_date'] ?? ''),
            'to_date'    => $this->security->cleanInput($_GET['to_date'] ?? ''),
            'min_amount' => $this->security->cleanInput($_GET['min_amount'] ?? ''),
            'max_amount' => $this->security->cleanInput($_GET['max_amount'] ?? ''),
            'sort'       => $this->security->cleanInput($_GET['sort'] ?? 'newest')
        ];
        $page     = max(1, intval($_GET['page'] ?? 1));
        $per_page = min(100, max(5, intval($_GET['per_page'] ?? 10)));
        $export   = $this->security->cleanInput($_GET['export'] ?? '');

        if ($export === 'csv') {
            $isAsync = filter_var($_GET['async'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($isAsync) {
                $adminId = \App\Shared\Security\Auth::user()['id'] ?? 0;
                \App\Shared\Core\Queue\QueueManager::getInstance()->dispatch(
                    new \App\Application\Jobs\ExportCsvJob([
                        'admin_id' => $adminId,
                        'type' => 'transactions',
                        'filters' => $filters
                    ])
                );
                http_response_code(202);
                echo json_encode(['success' => true, 'message' => 'Export job started asynchronously. You will be notified when it is ready.']);
                exit;
            }

            $generator = $this->service->getTransactions($filters, $page, $per_page, true);

            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="finzo_transactions_' . date('Y-m-d') . '.csv"');
            header('X-Accel-Buffering: no');

            $output = fopen('php://output', 'w');
            $headerWritten = false;
            foreach ($generator as $row) {
                if (!$headerWritten) {
                    fputcsv($output, array_keys($row), ',', '"', "\\");
                    $headerWritten = true;
                }
                fputcsv($output, $row, ',', '"', "\\");
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

    public function users(?string $id = null, ?string $extra = null): array
    {
        $filters = [
            'search'   => $this->security->cleanInput($_GET['search'] ?? ''),
            'status'   => $this->security->cleanInput($_GET['status'] ?? 'all'),
            'sort'     => $this->security->cleanInput($_GET['sort'] ?? 'newest'),
            'activity' => $this->security->cleanInput($_GET['activity'] ?? 'all')
        ];
        $page     = max(1, intval($_GET['page'] ?? 1));
        $per_page = min(50, max(10, intval($_GET['per_page'] ?? 20)));

        $data = $this->service->getUsersList($filters, $page, $per_page);
        $data['success'] = true;
        return $data;
    }

    public function exportUsers(): void
    {
        $filters = [
            'search'   => $this->security->cleanInput($_GET['search'] ?? ''),
            'status'   => $this->security->cleanInput($_GET['status'] ?? 'all'),
            'sort'     => $this->security->cleanInput($_GET['sort'] ?? 'newest'),
            'activity' => $this->security->cleanInput($_GET['activity'] ?? 'all')
        ];

        $isAsync = filter_var($_GET['async'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($isAsync) {
            $adminId = \App\Shared\Security\Auth::user()['id'] ?? 0;
            \App\Shared\Core\Queue\QueueManager::getInstance()->dispatch(
                new \App\Application\Jobs\ExportCsvJob([
                    'admin_id' => $adminId,
                    'type' => 'users',
                    'filters' => $filters
                ])
            );
            http_response_code(202);
            echo json_encode(['success' => true, 'message' => 'Export job started asynchronously. You will be notified when it is ready.']);
            exit;
        }

        $generator = $this->service->exportUsers($filters);

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="finzo_users_' . date('Y-m-d') . '.csv"');
        header('X-Accel-Buffering: no');

        $output = fopen('php://output', 'w');
        $headerWritten = false;
        foreach ($generator as $row) {
            if (!$headerWritten) {
                fputcsv($output, array_keys($row), ',', '"', "\\");
                $headerWritten = true;
            }
            fputcsv($output, $row, ',', '"', "\\");
        }
        fclose($output);
        if (defined('FINZO_TESTING') && FINZO_TESTING) {
            throw new \Exception('Exit called after export');
        }
        exit;
    }

    public function downloadExport(?string $id = null, ?string $extra = null): void
    {
        $filename = $this->security->cleanInput($_GET['file'] ?? '');
        
        // Strict path traversal prevention
        if (empty($filename) || basename($filename) !== $filename || strpos($filename, 'finzo_') !== 0 || pathinfo($filename, PATHINFO_EXTENSION) !== 'csv') {
            throw new ApiException('Invalid file requested', 400);
        }

        $exportDir = defined('FINZO_ROOT') ? FINZO_ROOT . '/storage/exports' : dirname(__DIR__, 4) . '/storage/exports';
        $filePath = $exportDir . '/' . $filename;

        if (!file_exists($filePath)) {
            throw new ApiException('File not found', 404);
        }

        // Output file securely
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $handle = fopen($filePath, 'rb');
        if ($handle) {
            while (!feof($handle)) {
                echo fread($handle, 8192);
                ob_flush();
                flush();
            }
            fclose($handle);
        }
        exit;
    }

    public function unlockAccount(?string $id = null, ?string $extra = null): array
    {
        $userId  = intval($_POST['user_id'] ?? 0);
        $adminId = \App\Shared\Security\Auth::user()['id'];
        if (!$userId) {
            throw new ApiException('User ID is required', 422);
        }

        $data = $this->service->unlockAccount($userId, $adminId);
        if (!$data['success']) {
            throw new ApiException($data['message'], $data['code'] ?? 500);
        }
        return $data;
    }

    public function userAction(?string $id = null, ?string $extra = null): array
    {
        $adminId = \App\Shared\Security\Auth::user()['id'];
        $userId = intval($id);

        $input = $_POST;
        if (empty($input)) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $input = $json;
            } else {
                parse_str($raw, $input);
            }
        }

        $action  = $this->security->cleanInput($input['action'] ?? '');
        $reason  = $this->security->cleanInput($input['reason'] ?? '');
        $password = $input['password'] ?? '';
        $balance_option = $this->security->cleanInput($input['balance_option'] ?? 'full');

        if (!$userId || \App\Shared\Core\Validation\Validator::isEmpty($action)) {
            throw new ApiException('User ID and action are required', 422);
        }

        $data = $this->service->processUserAction($userId, $action, $reason, $password, $balance_option, $adminId);
        if (!$data['success']) {
            throw new ApiException($data['message'], $data['code'] ?? 400);
        }
        return $data;
    }

    public function userDetail(?string $id = null, ?string $extra = null): array
    {
        $userId = intval($_GET['user_id'] ?? ($id ?? $_GET['id'] ?? null) ?? 0);
        if (!$userId) {
            throw new ApiException('User ID is required', 422);
        }

        $data = $this->service->getUserDetail($userId);
        if (!$data['success']) {
            throw new ApiException($data['message'], 404);
        }
        return $data;
    }

    public function suspendedUserDetail(?string $id = null, ?string $extra = null): array
    {
        $userId = intval($_GET['user_id'] ?? ($id ?? $_GET['id'] ?? null) ?? 0);
        if (!$userId) {
            throw new ApiException('User ID is required', 422);
        }

        $data = $this->service->getSuspendedUserDetail($userId);
        if (!$data['success']) {
            throw new ApiException($data['message'], 404);
        }
        return $data;
    }
    public function reverseTransaction(?string $id = null, ?string $extra = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }

        $transactionId = $id ?? $_POST['transaction_id'] ?? '';
        $reason = $this->security->cleanInput($_POST['reason'] ?? '');
        $adminId = \App\Shared\Security\Auth::user()['id'] ?? 0;

        if (empty($transactionId) || empty($reason)) {
            throw new ApiException('Transaction ID and reason are required', 422);
        }

        $result = $this->service->reverseTransaction($transactionId, $adminId, $reason);

        if (!$result['success']) {
            throw new ApiException($result['message'], $result['code'] ?? 422);
        }

        return $result;
    }
}

