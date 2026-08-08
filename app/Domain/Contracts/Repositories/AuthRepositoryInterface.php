<?php

namespace App\Domain\Contracts\Repositories;

interface AuthRepositoryInterface
{
    public function findUserByIdentity(string $identity): array|false;
    public function findUserByEmail(string $email): array|false;
    public function findUserByEmailAny(string $email): array|false;
    public function findUserByPhone(string $phone): array|false;
    public function fetchActiveAccount(string $customer_id): array|false;
    public function lockUser(int $id, int $attempts, string $lock_until): bool;
    public function updateFailedAttempts(int $id, int $attempts): bool;
    public function updateSuccessfulLogin(int $id, string $ip): bool;
    public function getNextCustomerId(): string;
    public function createUser(string $customer_id, string $fullName, string $email, string $phone, string $password_hash): string;
    public function createNotification(string $customer_id, string $type, string $title, string $message): bool;
    public function findAdminByEmail(string $email): array|false;
    public function autoUnlockAdmin(int $id): bool;
    public function lockAdmin(int $id): string|null;
    public function logAdminLock(string $adminId, string $ip, int $id): bool;
    public function updateAdminFailedAttempts(int $id, int $attempts): bool;
    public function updateAdminSuccessfulLogin(int $id, string $ip): bool;
    public function findStaffByIdentity(string $identity): array|false;
    public function lockStaff(int $id, int $attempts, string $lock_until): bool;
    public function updateStaffFailedAttempts(int $id, int $attempts): bool;
    public function updateStaffSuccessfulLogin(int $id, string $ip): bool;
    public function deletePasswordResets(string $customer_id): bool;
    public function createPasswordReset(string $customer_id, string $token, string $expires_at): bool;
    public function findPasswordReset(string $token): array|false;
    public function deletePasswordResetById(int $id): bool;
    public function resetUserPassword(string $customer_id, string $password_hash): bool;
    public function resetUserAttempts(string $customer_id): bool;
    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollBack(): bool;
}
