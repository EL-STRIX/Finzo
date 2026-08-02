<?php

namespace App\Domain\Contracts\Repositories;

interface LoanRepositoryInterface
{
    public function createLoanProduct(string $name, string $description, float $interestRate, float $minAmount, float $maxAmount, int $minTenure, int $maxTenure, string $status): int;
    public function getLoanProducts(string $status = 'all'): array;
    public function getLoanProductById(int $productId);
    public function createLoanApplication(int $userId, int $productId, float $amount, int $tenureMonths, string $purpose): int;
    public function getLoanApplications(array $filters = []): array;
    public function getLoanApplicationById(int $applicationId);
    public function getLoanApplicationForUpdate(int $applicationId);
    public function updateApplicationStatus(int $applicationId, string $status, string $adminNotes = ''): void;
    public function createActiveLoan(string $loanIdRef, int $userId, int $productId, int $applicationId, int $accountId, float $principal, float $interestRate, int $tenureMonths, float $totalPayable): int;
    
    public function insertLoanSchedule(int $loanId, int $installmentNo, string $dueDate, float $emiAmount, float $principalComponent, float $interestComponent, float $outstandingBalance): void;
    public function getLoanSchedules(int $loanId): array;
    public function getScheduleForUpdate(int $scheduleId);
    public function getLoanForUpdate(int $loanId);
    public function markSchedulePaid(int $scheduleId): void;
    public function recordRepayment(int $loanId, int $scheduleId, float $amount, string $transactionId): void;
    public function updateLoanTotals(int $loanId, float $amountPaid): void;
    
    public function updateLoanStatus(int $loanId, string $status): void;
    public function getLoanRepayments(int $loanId): array;
    public function getUnpaidPrincipalSum(int $loanId): float;
    public function cancelPendingSchedules(int $loanId): void;
}
