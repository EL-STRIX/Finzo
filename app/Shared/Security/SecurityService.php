<?php

namespace App\Shared\Security;

use PDO;

class SecurityService
{
    public function cleanInput($data)
    {
        return \clean_input($data);
    }

    public function validateCsrfToken(?string $token): bool
    {
        return \validate_csrf_token($token);
    }

    public function checkAdminPermission(int $adminId, string $permission): void
    {
        \check_admin_permission(\App\Shared\Core\Database::getInstance(), $adminId, $permission);
    }

    public function writeAuditLog(string $actionType, string|int|null $targetId, string $targetType, string|int|null $actorId, string $actorRole, string $details = '', ?string $ipAddress = null): void
    {
        \write_audit_log(\App\Shared\Core\Database::getInstance(), $actionType, $targetId ?? '', $targetType, $actorId ?? '', $actorRole, $details);
    }
}
