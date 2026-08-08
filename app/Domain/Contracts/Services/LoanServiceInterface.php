<?php

namespace App\Domain\Contracts\Services;

interface LoanServiceInterface
{
    public function createLoanProduct(array $data): array;
    public function getLoanProducts(string $status = 'all'): array;
    public function applyForLoan(int $userId, array $data): array;
    public function getLoanApplications(array $filters = []): array;
    public function approveApplication(int $applicationId, int $adminId, string $notes = ''): array;
    public function rejectApplication(int $applicationId, int $adminId, string $notes = ''): array;
    public function getLoanSchedules(int $loanId): array;
    public function processRepayment(int $userId, int $loanId, int $scheduleId): array;
    public function getLoanRepayments(int $loanId): array;
    public function forecloseLoan(int $loanId, int $adminId, float $penaltyRate): array;
}
