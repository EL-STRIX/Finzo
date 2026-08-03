<?php
namespace App\Presentation\Api\V1\Admin;

use App\Shared\Security\SecurityService;
use App\Application\Services\AdminStaffBranchService;
use App\Shared\Core\Exceptions\ApiException;

class AdminBranchController
{
    protected SecurityService $security;
    protected AdminStaffBranchService $service;

    public function __construct(\App\Domain\Contracts\Services\AdminStaffBranchServiceInterface $service, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security ?? new SecurityService();
        $this->service = $service;
    }



    public function deleteBranch(?string $id = null, ?string $extra = null): array
    {

        $adminId = \App\Shared\Security\Auth::user()['id'];
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

    public function departments(): array
    {
        $this->security->enforcePermission('view_branches');
        return $this->service->getDepartmentAnalytics();
    }
}
