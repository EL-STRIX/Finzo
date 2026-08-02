<?php

namespace App\Domain\Contracts\Services;

interface AuthServiceInterface
{
    public function loginUser(string $identity, string $password): array;
    public function loginAdmin(string $email, string $password): array;
    public function loginStaff(string $identity, string $password): array;
    public function forgotPassword(string $email): bool;
    public function resetPassword(string $token, string $newPassword): bool;
    public function registerUser(string $fullName, string $email, string $phone, string $password): bool;
}
