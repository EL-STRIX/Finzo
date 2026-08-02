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

    public function __construct(\App\Domain\Contracts\Repositories\StaffRepositoryInterface $repository, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security;
        $this->repository = $repository;
    }

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
    new \App\Application\Events\GenericAuditAction('staff_password_change', $staffId, 'staff', $staffId, 'staff', 'Staff changed their password')
);
        return true;
    }

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
        if ($account['status'] !== 'active') {
            throw new BusinessRuleException("Account is not active. Status: " . $account['status']);
        }
        return $account;
    }

    public function processDeposit(int $staffId, string $accountNumber, float $amount, string $reference_note)
    {
        try {
            $this->repository->beginTransaction();
            $account = $this->validateAccountForTransaction($accountNumber);

            $new_balance = (float)$account['balance'] + $amount;
            $this->repository->creditAccount($account['id'], $amount);

            $transactionId = generate_transaction_id();

            $this->repository->recordTransaction([
                'txn_id' => $transactionId,
                'from_account_id' => null,
                'to_account_id' => $account['id'],
                'type' => 'deposit',
                'amount' => $amount,
                'balance_after' => $new_balance,
                'description' => 'Cash Deposit',
                'reference_note' => $reference_note,
                'status' => 'completed',
                'processed_by_staff_id' => $staffId
            ]);

            send_notification(
                $this->repository->getPDO(),
                $account['customer_id'],
                'transaction',
                'Cash Deposit Received',
                'Cash deposit of ' . format_currency($amount) . ' credited to your account ' . $accountNumber . '. Transaction ID: ' . $transactionId
            );

            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('staff_deposit', $transactionId, 'transaction', $staffId, 'staff', "Deposit of " . format_currency($amount) . " to account {$accountNumber}")
);

            $this->repository->commit();

            return [
                'transaction_id' => $transactionId,
                'customer_name'  => $account['full_name'],
                'account_no'     => $accountNumber,
                'amount'         => $amount,
                'new_balance'    => $new_balance,
            ];
        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

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

            $new_balance = (float)$account['balance'] - $amount;
            $this->repository->debitAccount($account['id'], $amount);

            $transactionId = generate_transaction_id();

            $this->repository->recordTransaction([
                'txn_id' => $transactionId,
                'from_account_id' => $account['id'],
                'to_account_id' => null,
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_after' => $new_balance,
                'description' => 'Cash Withdrawal',
                'reference_note' => $identity_notes,
                'status' => 'completed',
                'processed_by_staff_id' => $staffId
            ]);

            send_notification(
                $this->repository->getPDO(),
                $account['customer_id'],
                'transaction',
                'Cash Withdrawal Processed',
                'Cash withdrawal of ' . format_currency($amount) . ' debited from your account ' . $accountNumber . '. Transaction ID: ' . $transactionId
            );

            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('staff_withdrawal', $transactionId, 'transaction', $staffId, 'staff', "Withdrawal of " . format_currency($amount) . " from account {$accountNumber}. Identity notes: {$identity_notes}")
);

            $this->repository->commit();

            return [
                'transaction_id' => $transactionId,
                'customer_name'  => $account['full_name'],
                'account_no'     => $accountNumber,
                'amount'         => $amount,
                'new_balance'    => $new_balance,
            ];
        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

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

    public function getReceipt(string $transactionId)
    {
        $transaction = $this->repository->getTransactionReceipt($transactionId);
        if (!$transaction) {
            throw new ResourceNotFoundException("Transaction not found");
        }
        return $transaction;
    }

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
                new \App\Application\Events\GenericAuditAction('staff_profile_update', $staffId, 'staff', $staffId, 'staff', 'Staff updated their profile')
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
