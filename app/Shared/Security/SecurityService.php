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

    public function checkAdminPermission(PDO $pdo, int $adminId, string $permission): void
    {
        \check_admin_permission($pdo, $adminId, $permission);
    }

    public function writeAuditLog(PDO $pdo, string $actionType, string $targetId, string $targetType, string $actorId, string $actorRole, string $details = '', ?string $ipAddress = null): void
    {
        \write_audit_log($pdo, $actionType, $targetId, $targetType, $actorId, $actorRole, $details);
    }
}
