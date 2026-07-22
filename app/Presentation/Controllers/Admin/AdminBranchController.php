<?php

namespace App\Presentation\Controllers\Admin;

use App\Shared\Security\SecurityService;

use App\Application\Services\AdminStaffBranchService;
use App\Shared\Core\Exceptions\ApiException;
use PDOException;
use Exception;

class AdminBranchController
{
    protected SecurityService $security;

    protected AdminStaffBranchService $service;

    public function __construct(AdminStaffBranchService $service, ?SecurityService $security = null)
    {
        $this->security = $security ?? new \App\Shared\Security\SecurityService();
        $this->service = $service;
    }

    protected function checkCsrf()
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';
        if (!$this->security->validateCsrfToken($csrf)) {
            throw new ApiException('Invalid CSRF token', 403);
        }
    }

    public function deleteBranch(): array
    {
        $this->checkCsrf();
        $admin_id = $_SESSION['admin_id'];
        $branch_id = intval($_POST['branch_id'] ?? 0);
        $admin_password = $_POST['admin_password'] ?? '';
        $confirm_name = $_POST['confirm_name'] ?? '';

        if (\App\Shared\Core\Validation\Validator::isEmpty($branch_id) || \App\Shared\Core\Validation\Validator::isEmpty($admin_password) || \App\Shared\Core\Validation\Validator::isEmpty($confirm_name)) {
            throw new ApiException('Missing required fields', 422);
        }

        try {
            $pdo = $this->service->getPDO();

            $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
            $stmt->execute([$admin_id]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($admin_password, $admin['password_hash'])) {
                throw new ApiException('Invalid admin password', 401);
            }

            $stmt = $pdo->prepare("SELECT id, name, type FROM branches WHERE id = ?");
            $stmt->execute([$branch_id]);
            $branch = $stmt->fetch();

            if (!$branch) {
                throw new ApiException('Branch not found', 404);
            }
            if ($branch['type'] === 'system') {
                throw new ApiException('System branches cannot be deleted', 403);
            }
            if (strtolower(trim($branch['name'])) !== strtolower(trim($confirm_name))) {
                throw new ApiException('Branch name confirmation does not match', 400);
            }

            $pdo->beginTransaction();

            if (\App\Shared\Core\Validation\Validator::isSet($_FILES['closure_document']) && $_FILES['closure_document']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['closure_document']['name'], PATHINFO_EXTENSION);
                $filename = 'closure_doc_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $dest_dir = UPLOAD_BRANCH_CERTS_PATH;
                if (!is_dir($dest_dir)) {
                    mkdir($dest_dir, 0755, true);
                }
                move_uploaded_file($_FILES['closure_document']['tmp_name'], $dest_dir . $filename);
            }

            $stmt = $pdo->prepare("UPDATE staff SET branch_id = NULL WHERE branch_id = ?");
            $stmt->execute([$branch_id]);

            $stmt = $pdo->prepare("DELETE FROM branches WHERE id = ?");
            $stmt->execute([$branch_id]);

            $this->security->writeAuditLog($pdo, 'branch_deleted', $branch['name'], 'branch', $admin_id, 'admin', "Deleted branch: {$branch['name']}");

            $pdo->commit();
            return ['success' => true, 'message' => 'Branch deleted successfully'];
        } catch (PDOException $e) {
            if (\App\Shared\Core\Validation\Validator::isSet($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new ApiException('Database error occurred', 500);
        }
    }

    public function getBranches(): array
    {
        $pdo = $this->service->getRepo()->getPDO();
        $search = $this->security->cleanInput($_GET['search'] ?? '');
        $type   = $this->security->cleanInput($_GET['type'] ?? 'all');
        $status = $this->security->cleanInput($_GET['status'] ?? 'all');

        $where_clauses = ["1=1"];
        $params = [];

        if ($type !== 'all' && in_array($type, ['system', 'custom'])) {
            $where_clauses[] = "b.type = ?";
            $params[] = $type;
        }
        if ($status !== 'all' && in_array($status, ['active', 'inactive'])) {
            $where_clauses[] = "b.status = ?";
            $params[] = $status;
        }
        if (!\App\Shared\Core\Validation\Validator::isEmpty($search)) {
            $where_clauses[] = "(b.name LIKE ? OR b.branch_code LIKE ? OR b.state LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $where_sql = implode(' AND ', $where_clauses);
        $stmt = $pdo->prepare("
            SELECT b.id, b.name, b.branch_code, b.state, b.city, b.address, b.description, b.type, b.status, b.cert_path, b.created_at,
                s.id as manager_id, s.full_name as manager_name, s.staff_id as manager_staff_id, s.personal_email as manager_email, s.profile_photo as manager_avatar,
                (SELECT COUNT(*) FROM staff st WHERE st.branch_id = b.id) as staff_count,
                (SELECT COUNT(*) FROM transactions t JOIN staff st ON t.processed_by_staff_id = st.id WHERE st.branch_id = b.id) as txn_count
            FROM branches b LEFT JOIN staff s ON b.manager_id = s.id WHERE {$where_sql} ORDER BY b.created_at DESC
        ");
        $stmt->execute($params);
        $branches = $stmt->fetchAll();

        $stmt_stats = $pdo->query("
            SELECT COUNT(*) as total, COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                (SELECT COUNT(*) FROM staff WHERE branch_id IS NOT NULL) as total_staff
            FROM branches
        ");
        $stats = $stmt_stats->fetch();

        foreach ($branches as &$b) {
            if (!\App\Shared\Core\Validation\Validator::isEmpty($b['manager_name'])) {
                $parts = explode(' ', $b['manager_name']);
                $b['manager_initials'] = strtoupper(substr($parts[0], 0, 1) . (\App\Shared\Core\Validation\Validator::isSet($parts[1]) ? substr($parts[1], 0, 1) : ''));
            } else {
                $b['manager_initials'] = '??';
            }
        }

        return ['success' => true, 'branches' => $branches, 'stats' => ['total' => (int)$stats['total'], 'active' => (int)$stats['active'], 'total_staff' => (int)$stats['total_staff']]];
    }
}
