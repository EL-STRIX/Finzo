<?php

namespace App\Domain\Contracts\Repositories;

interface AdminManagementRepositoryInterface
{
    public function getAdminByEmail(string $email);
    public function getAdminById(int $id);
    public function getAdminByAdminId(string $adminId);
    public function getMaxAdminIdNumber(): int;
    public function createAdmin(array $data);
    public function updateStatus(int $id, string $status);
    public function deleteAdmin(int $id);
    public function resetPassword(int $id, string $hash);
    public function getAdminDetail(int $id);
    public function getAdminActivity(string $adminId, int $limit = 50);
    public function getActivityCount(string $adminId);
    public function getAllAdmins();
    public function getLockedAdmins();
    public function updateAdminProfileField(int $id, string $field, $value);
    public function updateAdminPassword(int $id, string $hash);
    public function updateProfileFields(int $id, array $fields, array $params);
    public function getAdminProfile(int $id);
    public function updateTwoFa(int $id, int $enabled);
    public function unlockAdmin(int $id);
    public function updateSecurityQuestion(int $id, string $question, string $answerHash);
}

