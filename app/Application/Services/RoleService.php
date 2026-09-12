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
        $name = trim($data['name'] ?? '');
        if ($name === '') {
            throw new ValidationException(json_encode(['name' => 'Role name is required']));
        }
        if (mb_strlen($name) > 50) {
            throw new ValidationException(json_encode(['name' => 'Role name must not exceed 50 characters']));
        }

        $existing = $this->repository->getRoleByName($name);
        if ($existing) {
            throw new ValidationException(json_encode(['name' => 'Role name already exists']));
        }

        $description = !empty($data['description']) ? trim($data['description']) : null;
        if ($description !== null && mb_strlen($description) > 255) {
            throw new ValidationException(json_encode(['description' => 'Description must not exceed 255 characters']));
        }
        $roleId = $this->repository->createRole($name, $description);
        
        $permissionIds = $data['permissions'] ?? ($data['permission_ids'] ?? null);
        if (is_array($permissionIds)) {
            $permissionIds = array_map('intval', array_filter($permissionIds, 'is_numeric'));
            $this->repository->assignPermissionsToRole($roleId, $permissionIds);
        }

        $adminId = \App\Shared\Security\Auth::user()['id'] ?? 1;
        $this->security->writeAuditLog('role_created', $roleId, 'role', $adminId, 'admin', "Created role: {$name}");

        return $this->getRoleDetails($roleId);
    }

    public function updateRole(int $id, array $data): array
    {
        $role = $this->repository->getRoleById($id);
        if (!$role) {
            throw new ApiException('Role not found', 404);
        }
        
        $name = isset($data['name']) ? trim($data['name']) : $role['name'];
        if (mb_strlen($name) > 50) {
            throw new ValidationException(json_encode(['name' => 'Role name must not exceed 50 characters']));
        }
        if ($role['name'] === 'super_admin' && $name !== 'super_admin') {
            throw new ApiException('The super_admin role name cannot be modified', 403);
        }

        if ($name !== '' && $name !== $role['name']) {
            $existing = $this->repository->getRoleByName($name);
            if ($existing && (int)$existing['id'] !== $id) {
                throw new ValidationException(json_encode(['name' => 'Role name already exists']));
            }
        }

        $description = array_key_exists('description', $data) ? ($data['description'] !== null ? trim($data['description']) : null) : ($role['description'] ?? null);
        if ($description !== null && mb_strlen($description) > 255) {
            throw new ValidationException(json_encode(['description' => 'Description must not exceed 255 characters']));
        }
        $this->repository->updateRole($id, $name, $description);

        $permissionIds = $data['permissions'] ?? ($data['permission_ids'] ?? null);
        if (is_array($permissionIds)) {
            $permissionIds = array_map('intval', array_filter($permissionIds, 'is_numeric'));
            $this->repository->assignPermissionsToRole($id, $permissionIds);
        }

        $adminId = \App\Shared\Security\Auth::user()['id'] ?? 1;
        $this->security->writeAuditLog('role_updated', $id, 'role', $adminId, 'admin', "Updated role: {$name}");

        return $this->getRoleDetails($id);
    }

    public function deleteRole(int $id): array
    {
        $role = $this->repository->getRoleById($id);
        if (!$role) {
            throw new ApiException('Role not found', 404);
        }

        $protectedRoles = ['super_admin', 'admin', 'customer'];
        if (in_array(strtolower($role['name']), $protectedRoles, true)) {
            throw new ApiException("The '{$role['name']}' system role is protected and cannot be deleted", 403);
        }

        // Check if any admin or staff currently uses this role
        $db = \App\Shared\Core\Database::getInstance();
        $assignedAdmins = (int)$db->query("SELECT COUNT(*) FROM admin_users WHERE admin_role = " . $db->quote($role['name']) . " AND is_deleted = 0")->fetchColumn();
        $assignedStaff = (int)$db->query("SELECT COUNT(*) FROM staff WHERE designation = " . $db->quote($role['name']) . " AND status != 'deleted'")->fetchColumn();
        $assignedModels = (int)$db->query("SELECT COUNT(*) FROM model_has_roles WHERE role_id = " . (int)$id)->fetchColumn();

        if ($assignedAdmins > 0 || $assignedStaff > 0 || $assignedModels > 0) {
            $totalActive = $assignedAdmins + $assignedStaff + $assignedModels;
            throw new ApiException("Cannot delete role '{$role['name']}' because it is currently assigned to {$totalActive} active user(s). Reassign them before deleting.", 400);
        }

        $this->repository->deleteRole($id);
        
        $adminId = \App\Shared\Security\Auth::user()['id'] ?? 1;
        $this->security->writeAuditLog('role_deleted', $id, 'role', $adminId, 'admin', "Deleted role: {$role['name']}");

        return ['success' => true, 'message' => "Role '{$role['name']}' deleted successfully"];
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
