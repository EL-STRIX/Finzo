<?php

namespace App\Domain\Contracts\Repositories;

interface AdminStaffBranchRepositoryInterface
{
    public function getActiveBranches();
    public function findStaffByEmail(string $email);
    public function findBranchById(int $id);
    public function findAdminById(int $id);
    public function countBranchByCode(string $codePattern);
    public function countExactBranchCode(string $code);
    public function findActiveStaffById(int $id);
    public function insertBranch(array $data);
    public function updateStaffBranch(int $staffId, int $branchId);
    public function unassignStaffFromBranch(int $branchId);
    public function deleteBranch(int $branchId);
    public function getMaxStaffId();
    public function getLastStaffId();
    public function getStaffDetail(int $staffId);
    public function getStaffPerformance(int $staffId);
    public function findStaffRecord(int $staffId);
    public function updateStaffPassword(int $staffId, string $hash);
    public function suspendStaff(int $staffId, string $reason, string $suspended_by);
    public function reinstateStaff(int $staffId);
    public function deleteStaff(int $staffId);
    public function insertStaff(array $data);
    public function updateStaff(int $id, array $data);
    public function getBranchesList(string $search, string $type, string $status);
    public function getDepartmentAnalytics();
    public function updateStaffPhoto(int $staffId, string $path);
    public function getAdminPasswordHash(int $adminId): ?string;
    public function deleteBranchAndUnassignStaff(int $branchId): void;
    public function getStaffList(array $filters, int $page = 1, int $perPage = 20): array;
    public function exportStaffGenerator(array $filters): \Generator;
    public function getStaffDetailComprehensive(int $staffId): ?array;
}

