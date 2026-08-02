<?php

namespace App\Application\Services;

use App\Shared\Security\SecurityService;

use App\Infrastructure\Repositories\StaffRepository;
use App\Shared\Core\Exceptions\AuthenticationException;
use App\Shared\Core\Exceptions\BusinessRuleException;
use App\Shared\Core\Exceptions\ResourceNotFoundException;
use Exception;
use PDOException;
use finfo;

class StaffService implements \App\Domain\Contracts\Services\StaffServiceInterface
{
    protected SecurityService $security;

    private StaffRepository $repository;
    /**
     *   construct.
     *
     * @param \App\Domain\Contracts\Repositories\StaffRepositoryInterface $repository
     * @param ?\App\Domain\Contracts\Security\SecurityServiceInterface $security
     */
    public function __construct(\App\Domain\Contracts\Repositories\StaffRepositoryInterface $repository, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security;
        $this->repository = $repository;
    }
    /**
     * Account Lookup.
     *
     * @param string $accountNumber
     * @return mixed
     */
    public function accountLookup(string $accountNumber)
    {
        $result = $this->repository->findAccountByNo($accountNumber);
        if (!$result) {
            return ['found' => false];
        }

        $used_today = $this->repository->getUsedDailyLimit($accountNumber);
        $limit_left = max(0, $result['daily_limit'] - $used_today);

        return [
            'found' => true,
            'account' => [
                'name'         => $result['full_name'],
                'account_no'   => $result['account_no'],
                'account_type' => $result['account_type'],
                'status'       => $result['status'],
                'balance'      => $result['balance'],
                'daily_limit_left' => $limit_left
            ]
        ];
    }
    /**
     * Change Password.
     *
     * @param int $staffId
     * @param string $currentPassword
     * @param string $newPassword
     * @return mixed
     */
    public function changePassword(int $staffId, string $currentPassword, string $newPassword)
    {
        $hash = $this->repository->findStaffPasswordHash($staffId);
        if (!$hash) {
            throw new ResourceNotFoundException("Staff account not found");
        }

        if (!password_verify($currentPassword, $hash)) {
            throw new AuthenticationException("Current password is incorrect");
        }

        $new_hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->repository->updateStaffPassword($staffId, $new_hash);

        // write_audit_log requires pdo
        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction(\App\Domain\Enums\AuditActionEnum::STAFF_PASSWORD_CHANGE->value, $staffId, \App\Domain\Enums\RoleEnum::STAFF->value, $staffId, \App\Domain\Enums\RoleEnum::STAFF->value, 'Staff changed their password')
);
        return true;
    }
    /**
     * Get Dashboard Data.
     *
     * @param int $staffId
     * @return mixed
     */
    public function getDashboardData(int $staffId)
    {
        $deposits = $this->repository->getStaffShiftDeposits($staffId);
        $withdrawals = $this->repository->getStaffShiftWithdrawals($staffId);
        $total_today = $this->repository->getStaffTotalTransactionsToday($staffId);
        $recent_activity = $this->repository->getStaffRecentActivity($staffId);
        $staff_info = $this->repository->getStaffInfoWithBranch($staffId);

        return [
            'staff' => [
                'staff_id'      => $staff_info['staff_id'] ?? $_SESSION['staff_display_id'],
                'name'          => $staff_info['full_name'] ?? $_SESSION['staff_name'],
                'role'          => $staff_info['designation'] ?? 'Teller',
                'profile_photo' => $staff_info['profile_photo'] ?? null,
                'branch_name'   => $staff_info['branch_name'] ?? 'Unassigned'
            ],
            'shift_stats' => [
                'deposits_count'     => (int)$deposits['count'],
                'deposits_total'     => (float)$deposits['total'],
                'withdrawals_count'  => (int)$withdrawals['count'],
                'withdrawals_total'  => (float)$withdrawals['total'],
                'total_transactions' => $total_today,
                'net_cash_flow'      => (float)$deposits['total'] - (float)$withdrawals['total']
            ],
            'recent_activity' => $recent_activity
        ];
    }
    private function validateAccountForTransaction(string $accountNumber): array
    {
        $account = $this->repository->findAccountForUpdate($accountNumber);
        if (!$account) {
            throw new ResourceNotFoundException("Account not found");
        }
        if ($account['status'] !== \App\Domain\Enums\AccountStatusEnum::ACTIVE->value) {
            throw new BusinessRuleException("Account is not active. Status: " . $account['status']);
        }
        return $account;
    }
    private function executeTransactionCore(array $account, float $amount, string $type, string $note, int $staffId): array
    {
        $transactionId = generate_transaction_id();
        $isDeposit = $type === \App\Domain\Enums\TransactionTypeEnum::DEPOSIT->value;
        $new_balance = $isDeposit ? ((float)$account['balance'] + $amount) : ((float)$account['balance'] - $amount);
        
        if ($isDeposit) {
            $this->repository->creditAccount($account['id'], $amount);
        } else {
            $this->repository->debitAccount($account['id'], $amount);
        }

        $this->repository->recordTransaction([
            'txn_id' => $transactionId,
            'from_account_id' => $isDeposit ? null : $account['id'],
            'to_account_id' => $isDeposit ? $account['id'] : null,
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $new_balance,
            'description' => $isDeposit ? 'Cash Deposit' : 'Cash Withdrawal',
            'reference_note' => $note,
            'status' => \App\Domain\Enums\AccountStatusEnum::COMPLETED->value,
            'processed_by_staff_id' => $staffId
        ]);

        $notifTitle = $isDeposit ? 'Cash Deposit Received' : 'Cash Withdrawal Processed';
        $notifBody = $isDeposit 
            ? 'Cash deposit of ' . format_currency($amount) . ' credited to your account ' . $account['account_no'] . '. Transaction ID: ' . $transactionId
            : 'Cash withdrawal of ' . format_currency($amount) . ' debited from your account ' . $account['account_no'] . '. Transaction ID: ' . $transactionId;

        send_notification($this->repository->getPDO(), $account['customer_id'], \App\Domain\Enums\NotificationTypeEnum::TRANSACTION->value, $notifTitle, $notifBody);

        $auditAction = $isDeposit ? \App\Domain\Enums\AuditActionEnum::STAFF_DEPOSIT->value : \App\Domain\Enums\AuditActionEnum::STAFF_WITHDRAWAL->value;
        $auditBody = $isDeposit 
            ? "Deposit of " . format_currency($amount) . " to account {$account['account_no']}"
            : "Withdrawal of " . format_currency($amount) . " from account {$account['account_no']}. Identity notes: {$note}";

        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
            new \App\Application\Events\GenericAuditAction($auditAction, $transactionId, 'transaction', $staffId, \App\Domain\Enums\RoleEnum::STAFF->value, $auditBody)
        );

        return [
            'transaction_id' => $transactionId,
            'customer_name'  => $account['full_name'],
            'account_no'     => $account['account_no'],
            'amount'         => $amount,
            'new_balance'    => $new_balance,
        ];
    }

    /**
     * Process Deposit.
     *
     * @param int $staffId
     * @param string $accountNumber
     * @param float $amount
     * @param string $reference_note
     * @return mixed
     */
    public function processDeposit(int $staffId, string $accountNumber, float $amount, string $reference_note)
    {
        try {
            $this->repository->beginTransaction();
            $account = $this->validateAccountForTransaction($accountNumber);
            
            $result = $this->executeTransactionCore($account, $amount, \App\Domain\Enums\TransactionTypeEnum::DEPOSIT->value, $reference_note, $staffId);

            $this->repository->commit();
            return $result;
        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }
    /**
     * Process Withdrawal.
     *
     * @param int $staffId
     * @param string $accountNumber
     * @param float $amount
     * @param string $identity_notes
     * @return mixed
     */
    public function processWithdrawal(int $staffId, string $accountNumber, float $amount, string $identity_notes)
    {
        try {
            $this->repository->beginTransaction();
            $account = $this->validateAccountForTransaction($accountNumber);

            if ((float)$account['balance'] < $amount) {
                throw new BusinessRuleException("Insufficient balance. Available: " . format_currency($account['balance']));
            }

            $max_withdrawal = get_setting($this->repository->getPDO(), 'max_withdrawal_amount', 50000);
            if ($amount > $max_withdrawal) {
                throw new BusinessRuleException("Maximum withdrawal amount is " . format_currency($max_withdrawal));
            }

            $result = $this->executeTransactionCore($account, $amount, \App\Domain\Enums\TransactionTypeEnum::WITHDRAWAL->value, $identity_notes, $staffId);

            $this->repository->commit();
            return $result;
        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }
    /**
     * Get History.
     *
     * @param int $staffId
     * @param array $filters
     * @return mixed
     */
    public function getHistory(int $staffId, array $filters)
    {
        $where_clauses = ["t.processed_by_staff_id = ?"];
        $params = [$staffId];

        if ($filters['type'] !== 'all' && in_array($filters['type'], ['deposit', 'withdrawal'])) {
            $where_clauses[] = "t.type = ?";
            $params[] = $filters['type'];
        }
        if ($filters['status'] !== 'all' && in_array($filters['status'], ['completed', 'pending', 'failed', 'reversed'])) {
            $where_clauses[] = "t.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['from_date'])) {
            $where_clauses[] = "DATE(t.created_at) >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where_clauses[] = "DATE(t.created_at) <= ?";
            $params[] = $filters['to_date'];
        }
        if (!empty($filters['search'])) {
            $where_clauses[] = "(t.txn_id LIKE ? OR t.description LIKE ? OR a.account_no LIKE ? OR u.full_name LIKE ?)";
            $search_param = "%{$filters['search']}%";
            $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
        }

        $total = $this->repository->countTransactionsByStaff($staffId, $where_clauses, $params);
        $pages = max(1, ceil($total / $filters['per_page']));
        $offset = ($filters['page'] - 1) * $filters['per_page'];

        $transactions = $this->repository->getTransactionsByStaff($staffId, $where_clauses, $params, $filters['per_page'], $offset);

        return [
            'transactions' => $transactions,
            'total'        => $total,
            'pages'        => $pages
        ];
    }
    /**
     * Get Receipt.
     *
     * @param string $transactionId
     * @return mixed
     */
    public function getReceipt(string $transactionId)
    {
        $transaction = $this->repository->getTransactionReceipt($transactionId);
        if (!$transaction) {
            throw new ResourceNotFoundException("Transaction not found");
        }
        return $transaction;
    }
    /**
     * Get Full Profile.
     *
     * @param int $staffId
     * @return mixed
     */
    public function getFullProfile(int $staffId)
    {
        $staff = $this->repository->getFullStaffProfile($staffId);
        if (!$staff) {
            throw new ResourceNotFoundException("Staff profile not found");
        }

        $address = [
            'id' => $staff['id'],
            'street' => $staff['street'],
            'city' => $staff['city'],
            'state' => $staff['state'],
            'pincode' => $staff['pincode'],
            'country' => $staff['country']
        ];

        $documents = [];
        if (!empty($staff['id_type'])) {
            $documents[] = [
                'id' => $staff['id'],
                'id_type' => $staff['id_type'],
                'id_number' => $staff['id_number'],
                'front_image' => $staff['front_image'],
                'back_image' => $staff['back_image']
            ];
        }

        return [
            'staff'     => $staff,
            'address'   => $address,
            'documents' => $documents
        ];
    }
    /**
     * Update Profile.
     *
     * @param int $staffId
     * @param array $data
     * @param mixed $file
     * @return mixed
     */
    public function updateProfile(int $staffId, array $data, $file = null)
    {
        $fields = [];
        $params = [];

        if (isset($data['phone']) && $data['phone'] !== '') {
            $fields[] = 'phone = ?';
            $params[] = $data['phone'];
        }
        if (isset($data['emergency_contact_name']) && $data['emergency_contact_name'] !== '') {
            $fields[] = 'emergency_contact_name = ?';
            $params[] = $data['emergency_contact_name'];
        }
        if (isset($data['emergency_contact_phone']) && $data['emergency_contact_phone'] !== '') {
            $fields[] = 'emergency_contact_phone = ?';
            $params[] = $data['emergency_contact_phone'];
        }

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename  = 'staff_photo_' . uniqid() . '.' . $ext;
            $dest_path = UPLOAD_STAFF_PHOTOS_PATH . $filename;

            if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
                throw new \RuntimeException("Failed to save profile photo");
            }

            $new_photo_path = 'uploads/staff-photos/' . $filename;

            $old_photo = $this->repository->findStaffById($staffId)['profile_photo'] ?? null;
            if ($old_photo) {
                // Determine root path
                $root_dir = dirname(__DIR__, 2);
                $old_full = $root_dir . '/' . $old_photo;
                if (file_exists($old_full)) {
                    @unlink($old_full);
                }
            }

            $fields[] = 'profile_photo = ?';
            $params[] = $new_photo_path;
        }

        if (empty($fields)) {
            throw new BusinessRuleException("No fields provided to update");
        }

        try {
            $this->repository->updateStaffProfile($staffId, $fields, $params);
            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
                new \App\Application\Events\GenericAuditAction(\App\Domain\Enums\AuditActionEnum::STAFF_PROFILE_UPDATE->value, $staffId, \App\Domain\Enums\RoleEnum::STAFF->value, $staffId, \App\Domain\Enums\RoleEnum::STAFF->value, 'Staff updated their profile')
            );
        } catch (\PDOException $e) {
            // Clean up the newly uploaded photo if database update fails
            if (isset($dest_path) && file_exists($dest_path)) {
                @unlink($dest_path);
            }
            throw $e;
        }

        return $this->repository->getFullStaffProfile($staffId);
    }
}
