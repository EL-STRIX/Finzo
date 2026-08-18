<?php
namespace App\Presentation\Api\V1\Admin;

use App\Shared\Security\SecurityService;
use App\Shared\Security\Auth;
use App\Shared\Core\Exceptions\ApiException;

class AdminBranchController
{
    protected SecurityService $security;
    protected \App\Domain\Contracts\Services\AdminStaffBranchServiceInterface $service;

    public function __construct(\App\Domain\Contracts\Services\AdminStaffBranchServiceInterface $service, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security ?? new SecurityService();
        $this->service = $service;
    }

    public function createBranch(?string $id = null, ?string $extra = null): array
    {
        $user = Auth::user();
        $adminId = $user['id'] ?? ($_SESSION['admin_id_num'] ?? ($_SESSION['admin_id'] ?? 1));
        $adminPassword = $_POST['admin_password'] ?? '';

        $data = [
            'branch_name' => $this->security->cleanInput($_POST['branch_name'] ?? ($_POST['name'] ?? '')),
            'branch_code' => $this->security->cleanInput($_POST['branch_code'] ?? ''),
            'state'       => $this->security->cleanInput($_POST['state'] ?? ''),
            'city'        => $this->security->cleanInput($_POST['city'] ?? ''),
            'address'     => $this->security->cleanInput($_POST['address'] ?? ''),
            'manager_id'  => !empty($_POST['manager_id']) ? intval($_POST['manager_id']) : null,
            'description' => $this->security->cleanInput($_POST['description'] ?? '')
        ];

        $certFile = $_FILES['certificate'] ?? null;
        return $this->service->createBranch($data, $certFile, (int)$adminId, $adminPassword);
    }

    public function updateBranch(?string $id = null, ?string $extra = null): array
    {
        $user = Auth::user();
        $adminId = $user['id'] ?? ($_SESSION['admin_id_num'] ?? ($_SESSION['admin_id'] ?? 1));
        $branchId = intval($id ?: ($_POST['branch_id'] ?? 0));
        if (!$branchId) {
            throw new ApiException('Branch ID is required', 422);
        }

        $adminPassword = $_POST['admin_password'] ?? null;

        $data = [];
        if (isset($_POST['branch_name']) || isset($_POST['name'])) {
            $data['branch_name'] = $this->security->cleanInput($_POST['branch_name'] ?? $_POST['name']);
        }
        if (isset($_POST['state'])) {
            $data['state'] = $this->security->cleanInput($_POST['state']);
        }
        if (isset($_POST['city'])) {
            $data['city'] = $this->security->cleanInput($_POST['city']);
        }
        if (isset($_POST['address'])) {
            $data['address'] = $this->security->cleanInput($_POST['address']);
        }
        if (array_key_exists('manager_id', $_POST)) {
            $data['manager_id'] = !empty($_POST['manager_id']) ? intval($_POST['manager_id']) : null;
        }
        if (isset($_POST['description'])) {
            $data['description'] = $this->security->cleanInput($_POST['description']);
        }

        $certFile = $_FILES['certificate'] ?? null;
        return $this->service->updateBranch($branchId, $data, $certFile, (int)$adminId, $adminPassword);
    }

    public function getBranch(?string $id = null, ?string $extra = null): array
    {
        $branchId = intval($id);
        if (!$branchId) {
            throw new ApiException('Branch ID is required', 422);
        }

        $result = $this->service->getBranchById($branchId);
        if (!$result) {
            throw new ApiException('Branch not found', 404);
        }

        return $result;
    }

    public function generateCode(?string $id = null, ?string $extra = null): array
    {
        $state = $this->security->cleanInput($_GET['state'] ?? 'Default');
        $code = $this->service->generateBranchCode($state);
        return [
            'success'     => true,
            'branch_code' => $code
        ];
    }

    public function deleteBranch(?string $id = null, ?string $extra = null): array
    {
        $user = Auth::user();
        $adminId = $user['id'] ?? ($_SESSION['admin_id_num'] ?? ($_SESSION['admin_id'] ?? 1));
        $branchId = intval($id ?: ($_POST['branch_id'] ?? 0));
        $admin_password = $_POST['admin_password'] ?? '';
        $confirm_name = $_POST['confirm_name'] ?? '';

        if (empty($branchId) || empty($admin_password) || empty($confirm_name)) {
            throw new ApiException('Missing required fields for branch deletion', 422);
        }

        $closure_doc = $_FILES['closure_document'] ?? null;
        return $this->service->deleteBranch($branchId, (int)$adminId, $admin_password, $confirm_name, $closure_doc);
    }

    public function getBranches(?string $id = null, ?string $extra = null): array
    {
        $search = $this->security->cleanInput($_GET['search'] ?? '');
        $type   = $this->security->cleanInput($_GET['type'] ?? 'all');
        $status = $this->security->cleanInput($_GET['status'] ?? 'all');

        return $this->service->getBranchesList($search, $type, $status);
    }

    public function updateBranchStatus(?string $id = null, ?string $extra = null): array
    {
        $user = Auth::user();
        $adminId = $user['id'] ?? ($_SESSION['admin_id_num'] ?? ($_SESSION['admin_id'] ?? 1));
        $branchId = intval($id ?: ($_POST['branch_id'] ?? 0));
        $status = $this->security->cleanInput($_POST['status'] ?? '');
        $adminPassword = $_POST['admin_password'] ?? '';
        $reason = $this->security->cleanInput($_POST['reason'] ?? '');

        if (empty($branchId)) {
            throw new ApiException('Branch ID is required.', 422);
        }
        if (empty($status) || !in_array($status, ['active', 'inactive'])) {
            throw new ApiException('Valid status (active or inactive) is required.', 422);
        }
        if (empty($adminPassword)) {
            throw new ApiException('Admin password is required to change branch operational status.', 422);
        }

        return $this->service->toggleBranchStatus($branchId, $status, (int)$adminId, $adminPassword, $reason);
    }

    public function departments(): array
    {
        $this->security->enforcePermission('view_branches');
        return $this->service->getDepartmentAnalytics();
    }
}
