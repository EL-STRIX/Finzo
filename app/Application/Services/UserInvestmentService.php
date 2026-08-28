<?php

namespace App\Application\Services;

use App\Domain\Contracts\Repositories\UserInvestmentRepositoryInterface;
use App\Domain\Contracts\Repositories\UserRepositoryInterface;
use App\Shared\Core\Exceptions\ApiException;

class UserInvestmentService
{
    private UserInvestmentRepositoryInterface $repository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserInvestmentRepositoryInterface $repository,
        UserRepositoryInterface $userRepository
    ) {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
    }

    public function getUserInvestments(int $userId): array
    {
        return $this->repository->getFixedDeposits($userId);
    }

    public function createFixedDeposit(int $userId, float $amount, int $tenureMonths, int $autoRenew): array
    {
        if ($amount < 5000) {
            throw new ApiException('Minimum Fixed Deposit amount is ₹5,000.', 400);
        }
        if ($tenureMonths < 3 || $tenureMonths > 60) {
            throw new ApiException('Tenure must be between 3 and 60 months.', 400);
        }

        $user = $this->userRepository->getUserDashboardData($userId);
        if (!$user) {
            throw new ApiException('User not found.', 404);
        }

        $account = $this->userRepository->getActiveAccountByCustomerId($user['customer_id']);
        if (!$account || (float)$account['balance'] < $amount) {
            throw new ApiException('Insufficient account balance to open Fixed Deposit.', 400);
        }

        // Determine interest rate based on tenure
        $interestRate = 6.5; // base rate
        if ($tenureMonths >= 12 && $tenureMonths < 24) $interestRate = 7.25;
        elseif ($tenureMonths >= 24 && $tenureMonths < 36) $interestRate = 7.75;
        elseif ($tenureMonths >= 36) $interestRate = 8.10;

        // Compound interest calculation: A = P(1 + r/n)^(nt)
        $r = $interestRate / 100;
        $t = $tenureMonths / 12;
        $maturityAmount = round($amount * pow((1 + $r / 4), 4 * $t), 2);

        $maturityDate = date('Y-m-d', strtotime("+{$tenureMonths} months"));
        $fdNumber = 'FD' . date('Ymd') . rand(1000, 9999);

        // Deduct funds from savings account
        $pdo = \App\Shared\Core\Database::getInstance();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$amount, $account['id']]);

            // Create FD record
            $fdId = $this->repository->createFixedDeposit([
                'fd_number' => $fdNumber,
                'user_id' => $userId,
                'account_id' => $account['id'],
                'deposit_amount' => $amount,
                'interest_rate' => $interestRate,
                'tenure_months' => $tenureMonths,
                'maturity_amount' => $maturityAmount,
                'maturity_date' => $maturityDate,
                'auto_renew' => $autoRenew
            ]);

            // Insert transaction record
            $txnId = 'TXN' . date('Ymd') . rand(10000, 99999);
            $stmtTxn = $pdo->prepare("
                INSERT INTO transactions (account_id, txn_id, type, amount, description, status, created_at)
                VALUES (?, ?, 'transfer', ?, ?, 'completed', NOW())
            ");
            $stmtTxn->execute([$account['id'], $txnId, $amount, "Fixed Deposit Opening — {$fdNumber}"]);

            $pdo->commit();
            return ['success' => true, 'message' => 'Fixed Deposit opened successfully!', 'fd_number' => $fdNumber];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw new ApiException('Failed to process Fixed Deposit creation: ' . $e->getMessage(), 500);
        }
    }

    public function closeFixedDeposit(int $userId, int $fdId): array
    {
        $fd = $this->repository->getFixedDepositById($fdId, $userId);
        if (!$fd || $fd['status'] !== 'active') {
            throw new ApiException('Fixed Deposit not found or already closed.', 404);
        }

        // Premature closure penalty (1% reduction on interest)
        $amount = (float)$fd['deposit_amount'];
        $payout = $amount + round($amount * (($fd['interest_rate'] - 1.0) / 100) * ($fd['tenure_months'] / 12), 2);

        $pdo = \App\Shared\Core\Database::getInstance();
        $pdo->beginTransaction();
        try {
            // Credit funds back to savings account
            $stmt = $pdo->prepare("UPDATE accounts SET balance = balance + ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$payout, $fd['acc_id']]);

            // Update FD status
            $this->repository->updateFixedDepositStatus($fdId, 'closed');

            // Record transaction
            $txnId = 'TXN' . date('Ymd') . rand(10000, 99999);
            $stmtTxn = $pdo->prepare("
                INSERT INTO transactions (account_id, txn_id, type, amount, description, status, created_at)
                VALUES (?, ?, 'deposit', ?, ?, 'completed', NOW())
            ");
            $stmtTxn->execute([$fd['acc_id'], $txnId, $payout, "Fixed Deposit Premature Closure Payout — {$fd['fd_number']}"]);

            $pdo->commit();
            return ['success' => true, 'message' => "Fixed Deposit closed. Payout of ₹" . number_format($payout, 2) . " credited to your account."];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw new ApiException('Failed to close Fixed Deposit: ' . $e->getMessage(), 500);
        }
    }

    public function toggleAutoRenew(int $userId, int $fdId, int $autoRenew): array
    {
        $fd = $this->repository->getFixedDepositById($fdId, $userId);
        if (!$fd) {
            throw new ApiException('Fixed Deposit not found.', 404);
        }
        $this->repository->updateAutoRenew($fdId, $autoRenew);
        return ['success' => true, 'message' => 'Auto-renewal preference updated.'];
    }
}
