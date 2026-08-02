<?php

namespace App\Domain\Contracts\Services;

interface AdminStaffBranchServiceInterface
{
    public function getRepo(): AdminStaffBranchRepository;
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
}
