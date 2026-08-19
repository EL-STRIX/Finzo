<?php

namespace App\Domain\Contracts\Services;

interface AdminOperationsServiceInterface
{
    public function getAuditLog(array $filters, int $page, int $perPage): array;
    public function exportAuditLogs(array $filters): array|\Generator;
    public function getDashboardData(int $adminId): array;
    public function getDeletedUsers(array $filters, int $page, int $perPage): array;
    public function getInterestData(): array;
    public function updateInterestRate(float $new_rate, string $apply_from, string $custom_date, int $adminId): array;
    public function exportUsers(array $filters): array|\Generator;
    public function getLockedAccounts(array $filters, int $page, int $perPage): array;
    public function getLockedAccountDetail(int $userId): array;
    public function getPendingApprovals(array $filters, int $page, int $perPage): array;
    public function recoverUser(int $userId, int $adminId): array;
    public function getApplicationDetails(int $userId): array;
    public function processApplication(int $userId, string $action, string $reason, string $notes, int $adminId): array;
    public function getSuspendedUsers(array $filters, int $page, int $perPage): array;
    public function getTransactions(array $filters, int $page, int $perPage, bool $isExport = false): array|\Generator;
    public function getUsersList(array $filters, int $page, int $perPage): array;
    public function getSuspendedUserDetail(int $userId): array;
    public function unlockAccount(int $userId, int $adminId): array;
    public function processUserAction(int $userId, string $action, string $reason, string $password, string $balanceOption, int $adminId): array;
    public function getUserDetail(int $userId): array;
}
