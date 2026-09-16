<?php

namespace App\Presentation\Api\V1\Admin;

use App\Domain\Contracts\Services\RoleServiceInterface;
use App\Shared\Security\SecurityService;

class RoleController
{
    private RoleServiceInterface $service;
    private SecurityService $security;

    public function __construct(RoleServiceInterface $service, ?SecurityService $security = null)
    {
        $this->service = $service;
        $this->security = $security ?? new SecurityService();
    }

    private function checkAccess(): void
    {
        $admin = \App\Shared\Security\Auth::user();
        $adminId = $admin['id'] ?? ($_SESSION['admin_id'] ?? 0);
        if (!$adminId) {
            throw new \App\Shared\Core\Exceptions\ApiException('Unauthorized access', 401);
        }

        // Super admins have universal access; other admins check manage_admins or manage_roles
        // Note: Auth::user() exposes the role under the 'role' key (not 'admin_role')
        $adminRole = strtolower((string)($admin['role'] ?? ($_SESSION['admin_role'] ?? '')));
        if ($adminRole === 'super_admin') {
            return;
        }

        try {
            $this->security->checkAdminPermission((int)$adminId, 'manage_admins');
        } catch (\Throwable $e) {
            $this->security->checkAdminPermission((int)$adminId, 'manage_roles');
        }
    }

    private function getRequestInput(): array
    {
        $input = $_POST;
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $input = array_merge($input, $json);
            } else {
                parse_str($raw, $parsed);
                if (is_array($parsed)) {
                    $input = array_merge($input, $parsed);
                }
            }
        }
        return $input;
    }

    public function index(): array
    {
        $this->checkAccess();
        return ['success' => true, 'data' => $this->service->getRoles()];
    }

    public function show(?string $id = null): array
    {
        $this->checkAccess();
        $roleId = (int)($id ?? $_GET['id'] ?? 0);
        return ['success' => true, 'data' => $this->service->getRoleDetails($roleId)];
    }

    public function create(): array
    {
        $this->checkAccess();
        $input = $this->getRequestInput();

        $result = $this->service->createRole($input);
        if (!headers_sent()) {
            http_response_code(201);
        }
        return ['success' => true, 'message' => 'Role created successfully', 'data' => $result];
    }

    public function update(?string $id = null): array
    {
        $this->checkAccess();
        $roleId = (int)($id ?? $_GET['id'] ?? 0);
        $input = $this->getRequestInput();

        $result = $this->service->updateRole($roleId, $input);
        return ['success' => true, 'message' => 'Role updated successfully', 'data' => $result];
    }

    public function destroy(?string $id = null): array
    {
        $this->checkAccess();
        $roleId = (int)($id ?? $_GET['id'] ?? 0);
        return $this->service->deleteRole($roleId);
    }

    public function permissions(): array
    {
        $this->checkAccess();
        return ['success' => true, 'data' => $this->service->getPermissions()];
    }

    public function assignRoles(?string $modelType = null, ?string $modelId = null): array
    {
        $this->checkAccess();
        $input = $this->getRequestInput();
        
        $type = $this->security->cleanInput($modelType ?? $input['model_type'] ?? '');
        $id = (int)($modelId ?? $input['model_id'] ?? 0);
        $roleIds = $input['role_ids'] ?? ($input['roles'] ?? []);
        
        if (!is_array($roleIds)) {
            $roleIds = [$roleIds];
        }
        
        return $this->service->syncModelRoles($type, $id, $roleIds);
    }
}
