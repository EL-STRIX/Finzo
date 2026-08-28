<?php

namespace App\Application\Services;

use App\Infrastructure\Repositories\UserBillPaymentRepository;
use App\Domain\Contracts\Repositories\UserRepositoryInterface;
use App\Shared\Core\Exceptions\ApiException;

class UserBillPaymentService
{
    private UserBillPaymentRepository $repository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserBillPaymentRepository $repository,
        UserRepositoryInterface $userRepository
    ) {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
    }

    public function getBillers(int $userId): array
    {
        return $this->repository->getSavedBillers($userId);
    }

    public function getHistory(int $userId): array
    {
        return $this->repository->getPaymentHistory($userId);
    }

    public function payBill(int $userId, string $category, string $billerName, string $consumerNumber, float $amount): array
    {
        if ($amount <= 0) {
            throw new ApiException('Payment amount must be greater than zero.', 400);
        }
        if (empty($billerName) || empty($consumerNumber)) {
            throw new ApiException('Biller name and Consumer Number are required.', 400);
        }

        $user = $this->userRepository->getUserDashboardData($userId);
        if (!$user) {
            throw new ApiException('User not found.', 404);
        }

        $account = $this->userRepository->getActiveAccountByCustomerId($user['customer_id']);
        if (!$account || (float)$account['balance'] < $amount) {
            throw new ApiException('Insufficient account balance for bill payment.', 400);
        }

        $pdo = \App\Shared\Core\Database::getInstance();
        $pdo->beginTransaction();
        try {
            // Deduct funds from savings account
            $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$amount, $account['id']]);

            // Save bill payment record
            $refNo = 'BILL' . date('Ymd') . rand(10000, 99999);
            $this->repository->createBillPayment($userId, $billerName, $category, $consumerNumber, $amount, $refNo);

            // Record transaction
            $stmtTxn = $pdo->prepare("
                INSERT INTO transactions (account_id, txn_id, type, amount, description, status, created_at)
                VALUES (?, ?, 'withdrawal', ?, ?, 'completed', NOW())
            ");
            $stmtTxn->execute([$account['id'], $refNo, $amount, "Utility Bill Payment — {$billerName} ({$consumerNumber})"]);

            // Save biller if not already saved
            $this->repository->createBiller($userId, $category, $billerName, $consumerNumber);

            $pdo->commit();
            return ['success' => true, 'message' => "Bill payment of ₹" . number_format($amount, 2) . " successful!", 'reference_no' => $refNo];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw new ApiException('Bill payment failed: ' . $e->getMessage(), 500);
        }
    }
}
