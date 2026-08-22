<?php

namespace App\Domain\Contracts\Services;

interface AdminManagementServiceInterface
{
    public function requireSuperAdmin(): void;
    public function getNextAdminId(): string;
    public function addAdmin(array $data, ?array $file): array;
    public function handleAdminAction(string $action, int $target_numeric_id): array;
    public function getAdminDetail(int $target_id): array;
    public function getAdminProfileForUser(int $adminId): array;
    public function getAdmins(): array;
    public function getLockedAdmins(): array;
    public function uploadAvatar(int $adminId, ?array $file, bool $isProfileEndpoint = false): array;
    public function updateTwoFaSettings(int $adminId, int $enabled): array;
    public function updateAdminProfile(int $adminId, array $data): array;
    public function updateSecurityQuestion(int $adminId, array $data): array;
    public function unlockAdmin(int $targetAdminId): array;
}

