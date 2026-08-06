<?php

namespace App\Shared\Security;

use PDO;

class SecurityService implements \App\Domain\Contracts\Security\SecurityServiceInterface
{
    public function cleanInput($data)
    {
        return \clean_input($data);
    }

    public function validateCsrfToken(?string $token): bool
    {
        return \validate_csrf_token($token);
    }

    public function checkPermission(string $modelType, int $modelId, string $permission): void
    {
        $rbac = \App\Shared\Security\RbacService::getInstance();
        if (!$rbac->can($modelType, $modelId, $permission)) {
            throw new \App\Shared\Core\Exceptions\ApiException('You do not have permission to perform this action', 403);
        }
    }

    public function checkAdminPermission(int $adminId, string $permission): void
    {
        $this->checkPermission('admin', $adminId, $permission);
    }

    public function enforcePermission(string $permission): void
    {
        $adminId = \App\Shared\Security\Auth::user()['id'] ?? 0;
        if (!$adminId) {
            throw new \App\Shared\Core\Exceptions\ApiException('Unauthorized access', 401);
        }
        $this->checkAdminPermission($adminId, $permission);
    }

    public function writeAuditLog(string $actionType, string|int|null $targetId, string $targetType, string|int|null $actorId, string $actorRole, string $details = '', ?string $ipAddress = null): void
    {
        \write_audit_log(\App\Shared\Core\Database::getInstance(), $actionType, $targetId ?? '', $targetType, $actorId ?? '', $actorRole, $details);
    }
}
