<?php

namespace App\Domain\Contracts\Services;

interface AdminStaffBranchServiceInterface
{
    public function getRepo(): \App\Domain\Contracts\Repositories\AdminStaffBranchRepositoryInterface;
    public function handleStaffUpload($file, $prefix, $dir, &$errors, $field, $required = false);
    public function validateStaffData(array $data);
    public function generateBranchCode($state);
    public function generateStaffId(): string;
    public function createStaff(array $data, array $files, int $adminId): array;
    public function verifyAdminPassword(int $adminId, string $password);
    public function generateStrongPassword($length = 12);
    public function getStaffDetail(...$args);
    public function getStaffPerformance(...$args);
    public function deleteBranch(int $branchId, int $adminId, string $admin_password, string $confirm_name, ?array $closure_doc): array;
    public function getBranchesList(string $search, string $type, string $status): array;
    public function getDepartmentAnalytics(): array;
    public function getStaffList(array $filters, int $page = 1, int $perPage = 20): array;
    public function exportStaff(array $filters): \Generator;
    public function getStaffDetailComprehensive(int $staffId): ?array;
    public function createBranch(array $data, ?array $certFile, int $adminId, string $adminPassword): array;
    public function updateBranch(int $branchId, array $data, ?array $certFile, int $adminId, ?string $adminPassword): array;
    public function getBranchById(int $branchId): ?array;
    public function executeStaffAction(int $staffId, string $action, ?string $reason, int $adminId, string $adminPassword): array;
}

