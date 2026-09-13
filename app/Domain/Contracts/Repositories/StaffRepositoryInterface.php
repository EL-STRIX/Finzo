<?php

namespace App\Domain\Contracts\Repositories;

interface StaffRepositoryInterface
{
    public function findAccountByNo(string $accountNumber);
    public function getUsedDailyLimit(string $accountNumber);
    public function findStaffById(int $staffId);
    public function findStaffPasswordHash(int $staffId);
    public function updateStaffPassword(int $staffId, string $new_hash): bool;
    public function getStaffShiftDeposits(int $staffId);
    public function getStaffShiftWithdrawals(int $staffId);
    public function getStaffTotalTransactionsToday(int $staffId);
    public function getStaffRecentActivity(int $staffId);
    public function getStaffInfoWithBranch(int $staffId);
    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollBack(): bool;
    public function findAccountForUpdate(string $accountNumber);
    public function creditAccount(int $account_id, float $amount);
    public function debitAccount(int $account_id, float $amount);
    public function recordTransaction(array $data);
    public function countTransactionsByStaff(int $staffId, array $where_clauses, array $params);
    public function getTransactionsByStaff(int $staffId, array $where_clauses, array $params, int $per_page, int $offset);
    public function getTransactionReceipt(string $transactionId);
    public function getFullStaffProfile(int $staffId);
    public function updateStaffProfile(int $staffId, array $fields, array $params);
    public function clockIn(int $staffId, string $staffIdentifier): bool;
    public function clockOut(int $staffId, string $staffIdentifier, string $notes): bool;
    public function getCurrentShift(string $staffIdentifier): ?array;
}
