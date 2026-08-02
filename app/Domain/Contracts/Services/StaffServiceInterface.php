<?php

namespace App\Domain\Contracts\Services;

interface StaffServiceInterface
{
    public function accountLookup(string $accountNumber);
    public function changePassword(int $staffId, string $currentPassword, string $newPassword);
    public function getDashboardData(int $staffId);
    public function processDeposit(int $staffId, string $accountNumber, float $amount, string $reference_note);
    public function processWithdrawal(int $staffId, string $accountNumber, float $amount, string $identity_notes);
    public function getHistory(int $staffId, array $filters);
    public function getReceipt(string $transactionId);
    public function getFullProfile(int $staffId);
    public function updateProfile(int $staffId, array $data, $file = null);
}
