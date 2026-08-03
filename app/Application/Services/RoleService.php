<?php

namespace App\Application\Services;

use App\Domain\Contracts\Repositories\RoleRepositoryInterface;
use App\Domain\Contracts\Services\RoleServiceInterface;
use App\Shared\Core\Exceptions\ApiException;
use App\Domain\Exceptions\ValidationException;

class RoleService implements RoleServiceInterface
{
    private RoleRepositoryInterface $repository;
    private \App\Shared\Security\SecurityService $security;

    public function __construct(RoleRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->security = new \App\Shared\Security\SecurityService();
    }

    public function getRoles(): array
    {
        return $this->repository->getAllRoles();
    }

    public function getRoleDetails(int $id): array
    {
        $role = $this->repository->getRoleById($id);
        if (!$role) {
            throw new ApiException('Role not found', 404);
        }
        
        $role['permissions'] = $this->repository->getRolePermissions($id);
        return $role;
    }

    public function createRole(array $data): array
    {
        if (empty($data['name'])) {
            throw new ValidationException(json_encode(['name' => 'Role name is required']));
        }

        $existing = $this->repository->getRoleByName($data['name']);
        if ($existing) {
            throw new ValidationException(json_encode(['name' => 'Role name already exists']));
        }

        $roleId = $this->repository->createRole($data['name']);
        
        if (isset($data['permission_ids']) && is_array($data['permission_ids'])) {
            $this->repository->assignPermissionsToRole($roleId, $data['permission_ids']);
        }

        $this->security->writeAuditLog('role_created', $roleId, 'role', \App\Shared\Security\Auth::user()['id'], 'admin');

        return $this->getRoleDetails($roleId);
    }

    public function updateRole(int $id, array $data): array
    {
        $role = $this->repository->getRoleById($id);
        if (!$role) {
            throw new ApiException('Role not found', 404);
        }
        
        if ($role['name'] === 'super_admin') {
            throw new ApiException('The super_admin role cannot be modified', 403);
        }

        if (!empty($data['name']) && $data['name'] !== $role['name']) {
            $existing = $this->repository->getRoleByName($data['name']);
            if ($existing && $existing['id'] !== $id) {
                throw new ValidationException(json_encode(['name' => 'Role name already exists']));
            }
            $this->repository->updateRole($id, $data['name']);
        }

        if (isset($data['permission_ids']) && is_array($data['permission_ids'])) {
            $this->repository->assignPermissionsToRole($id, $data['permission_ids']);
        }

        $this->security->writeAuditLog('role_updated', $id, 'role', \App\Shared\Security\Auth::user()['id'], 'admin');

        return $this->getRoleDetails($id);
    }

    public function deleteRole(int $id): array
    {
        $role = $this->repository->getRoleById($id);
        if (!$role) {
            throw new ApiException('Role not found', 404);
        }

        if ($role['name'] === 'super_admin') {
            throw new ApiException('The super_admin role cannot be deleted', 403);
        }

        $this->repository->deleteRole($id);
        
        $this->security->writeAuditLog('role_deleted', $id, 'role', \App\Shared\Security\Auth::user()['id'], 'admin');

        return ['success' => true, 'message' => 'Role deleted successfully'];
    }

    public function getPermissions(): array
    {
        return $this->repository->getAllPermissions();
    }

    public function getModelRoles(string $modelType, int $modelId): array
    {
        if (!in_array($modelType, ['admin', 'staff', 'user'])) {
            throw new ApiException('Invalid model type', 422);
        }
        return $this->repository->getModelRoles($modelType, $modelId);
    }

    public function syncModelRoles(string $modelType, int $modelId, array $roleIds): array
    {
        if (!in_array($modelType, ['admin', 'staff', 'user'])) {
            throw new ApiException('Invalid model type', 422);
        }
        
        $this->repository->syncModelRoles($modelType, $modelId, $roleIds);
        
        $this->security->writeAuditLog('roles_synced', $modelId, $modelType, \App\Shared\Security\Auth::user()['id'], 'admin', "Synced roles: " . implode(',', $roleIds));

        return ['success' => true, 'message' => 'Roles synced successfully'];
    }
}
