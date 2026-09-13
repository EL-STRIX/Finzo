<?php

namespace App\Domain\Contracts\Services;

interface UserServiceInterface
{
    public function getRepository(): \App\Domain\Contracts\Repositories\UserRepositoryInterface;
    public function validateKyc(array $data, array $files);
    public function processKycUploads($files, $userId, $existing_files);
    public function fetchAccountLookup(...$args);
    public function fetchAccountSettings(...$args);
    public function fetchUsedToday(...$args);
    public function updateDailyLimit(...$args);
    public function fetchAccountDetails(...$args);
    public function fetchMiniStatement(...$args);
    public function fetchTodayTransferTotal(...$args);
    public function getCustomerId(...$args);
    public function fetchBeneficiaries(...$args);
    public function deleteBeneficiary(...$args);
    public function getDb(...$args);
    public function countBeneficiaries(...$args);
    public function getBeneficiaryByAccount(...$args);
    public function addBeneficiary(...$args);
    public function getUserPasswordHash(...$args);
    public function updatePassword(...$args);
    public function getTxnPinHash(...$args);
    public function updateTxnPin(...$args);
    public function changeTxnPin(int $userId, array $data): bool;
    public function getUserFiles(...$args);
    public function getExistingAccountByCustomerId(...$args);
    public function updateAccountResubmission(...$args);
    public function insertPendingAccount(...$args);
    public function getNextKycApplicationNo(...$args);
    public function getKycApplication(...$args);
    public function getUserDashboardData(...$args);
    public function getActiveAccountByCustomerId(...$args);
    public function getRecentTransactions(...$args);
    public function getTransactionToAccountId(...$args);
    public function getMonthlyIncome(...$args);
    public function getMonthlyExpenses(...$args);
    public function getUnreadNotificationCount(...$args);
    public function getPastRejections(...$args);
    public function getNotificationsTotal(...$args);
    public function getNotifications(...$args);
    public function markNotificationRead(...$args);
    public function getTransactions(int $accountId, array $filters): array;
    public function markAllNotificationsRead(...$args);
    public function deleteNotification(...$args);
    public function clearAllNotifications(...$args);
    public function getFullProfile(...$args);
    public function checkEmailExists(...$args);
    public function checkPhoneExists(...$args);
    public function updateProfile(...$args);
    public function getActiveAccount(...$args);
    public function getStatementTransactions(...$args);
    public function getOpeningBalance(...$args);
    public function getTransactionsCount(...$args);
    public function getTransactionsList(...$args);
    public function getAccountIdByNumber(...$args);
    public function getAccountForUpdate(...$args);
    public function updateAccountBalance(...$args);
    public function insertTransaction(...$args);
    public function checkDuplicateTransaction(int $fromAccountId, float $amount, string $type): void;
    public function checkDailyTransferLimit(int $fromAccountId, float $amount, float $dailyLimit): void;
}
