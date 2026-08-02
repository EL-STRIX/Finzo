<?php

namespace App\Domain\Contracts\Repositories;

interface UserRepositoryInterface
{
    public function fetchAccountLookup(string $accountNumber);
    public function fetchAccountSettings(int $userId);
    public function fetchUsedToday(int $account_id);
    public function updateDailyLimit(int $account_id, float $daily_limit);
    public function fetchAccountDetails(int $userId);
    public function fetchMiniStatement(int $account_id);
    public function fetchTodayTransferTotal(int $account_id);
    public function getCustomerId(int $userId);
    public function fetchBeneficiaries($userId);
    public function deleteBeneficiary($beneficiary_id, $userId);
    public function countBeneficiaries($userId);
    public function getBeneficiaryByAccount($userId, $accountNumber);
    public function addBeneficiary($userId, $nickname, $accountNumber, $bank_name);
    public function getUserPasswordHash(int $userId);
    public function updatePassword(int $userId, string $new_hash);
    public function getUserFiles(int $userId);
    public function getExistingAccountByCustomerId(string $customer_id);
    public function updateAccountResubmission(string $account_type, float $initial_deposit, string $txn_pin_hash, string $customer_id);
    public function insertPendingAccount(string $customer_id, string $temp_account_no, string $account_type, float $initial_deposit, string $txn_pin_hash);
    public function getNextKycApplicationNo(string $year);
    public function getKycApplication(string $customer_id_str);
    public function executeQuery($sql, $params = []);
    public function getUserDashboardData(int $userId);
    public function getActiveAccountByCustomerId(string $customer_id);
    public function getRecentTransactions(int $account_id);
    public function getTransactionToAccountId(string $transactionId);
    public function getMonthlyIncome(int $account_id);
    public function getMonthlyExpenses(int $account_id);
    public function getUnreadNotificationCount($customer_id);
    public function getPastRejections(string $customer_id);
    public function getNotificationsTotal($customer_id);
    public function getNotifications($customer_id, int $per_page, int $offset);
    public function markNotificationRead(int $notification_id, $customer_id);
    public function markAllNotificationsRead($customer_id);
    public function deleteNotification(int $notification_id, $customer_id);
    public function clearAllNotifications($customer_id);
    public function getFullProfile(int $userId);
    public function checkEmailExists(string $email, int $exclude_user_id);
    public function checkPhoneExists(string $phone, int $exclude_user_id);
    public function updateProfile(int $userId, string $fullName, string $phone, string $email);
    public function getActiveAccount(int $userId);
    public function getStatementTransactions(int $account_id, string $fromDate, string $toDate);
    public function getOpeningBalance(int $account_id, string $fromDate);
    public function getTransactionsCount($where_sql, $params);
    public function getTransactionsList($where_sql, $params, $per_page, $offset);
    public function getAccountForUpdate(int $account_id);
    public function getAccountIdByNumber(string $accountNumber);
    public function updateAccountBalance(int $account_id, float $amount_change);
    public function insertTransaction(string $transactionId, int $from_id, int $to_id, float $amount, float $balance_after, float $dest_balance_after, string $description);
}
