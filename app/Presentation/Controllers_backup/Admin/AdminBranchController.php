<?php
namespace App\Presentation\Controllers\Admin;

use App\Shared\Security\SecurityService;
use App\Application\Services\AdminStaffBranchService;
use App\Shared\Core\Exceptions\ApiException;

class AdminBranchController
{
    protected SecurityService $security;
    protected AdminStaffBranchService $service;

    public function __construct(AdminStaffBranchService $service, ?SecurityService $security = null)
    {
        $this->security = $security ?? new SecurityService();
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

    public function deleteBranch(?string $id = null, ?string $extra = null): array
    {
        $this->checkCsrf();
        $adminId = $_SESSION['admin_id'];
        $branchId = intval($_POST['branch_id'] ?? 0);
        $admin_password = $_POST['admin_password'] ?? '';
        $confirm_name = $_POST['confirm_name'] ?? '';

        if (empty($branchId) || empty($admin_password) || empty($confirm_name)) {
            throw new ApiException('Missing required fields', 422);
        }

        $closure_doc = $_FILES['closure_document'] ?? null;
        return $this->service->deleteBranch($branchId, $adminId, $admin_password, $confirm_name, $closure_doc);
    }

    public function getBranches(?string $id = null, ?string $extra = null): array
    {
        $search = $this->security->cleanInput($_GET['search'] ?? '');
        $type   = $this->security->cleanInput($_GET['type'] ?? 'all');
        $status = $this->security->cleanInput($_GET['status'] ?? 'all');

        return $this->service->getBranchesList($search, $type, $status);
    }
}