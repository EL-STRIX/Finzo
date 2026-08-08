<?php

namespace App\Domain\Contracts\Repositories;

interface RoleRepositoryInterface
{
    public function getAllRoles(): array;
    public function getRoleById(int $id): ?array;
    public function getRoleByName(string $name): ?array;
    public function createRole(string $name): int;
    public function updateRole(int $id, string $name): bool;
    public function deleteRole(int $id): bool;
    
    public function getAllPermissions(): array;
    public function getRolePermissions(int $roleId): array;
    public function assignPermissionsToRole(int $roleId, array $permissionIds): void;
    
    public function getModelRoles(string $modelType, int $modelId): array;
    public function syncModelRoles(string $modelType, int $modelId, array $roleIds): void;
}
