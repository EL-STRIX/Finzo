<?php

namespace App\Domain\Contracts\Services;

interface RoleServiceInterface
{
    public function getRoles(): array;
    public function getRoleDetails(int $id): array;
    public function createRole(array $data): array;
    public function updateRole(int $id, array $data): array;
    public function deleteRole(int $id): array;
    
    public function getPermissions(): array;
    
    public function getModelRoles(string $modelType, int $modelId): array;
    public function syncModelRoles(string $modelType, int $modelId, array $roleIds): array;
}
