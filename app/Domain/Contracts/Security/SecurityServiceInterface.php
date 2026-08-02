<?php

namespace App\Domain\Contracts\Security;

interface SecurityServiceInterface
{
    public function cleanInput($data);
    public function validateCsrfToken(?string $token): bool;
    public function checkPermission(string $modelType, int $modelId, string $permission): void;
    public function checkAdminPermission(int $adminId, string $permission): void;
    public function writeAuditLog(string $actionType, string|int|null $targetId, string $targetType, string|int|null $actorId, string $actorRole, string $details = '', ?string $ipAddress = null): void;
}
