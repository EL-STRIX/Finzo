<?php

namespace App\Application\Actions;

use App\Domain\Contracts\Services\UserServiceInterface;
use App\Domain\Contracts\Security\SecurityServiceInterface;
use App\Domain\Enums\NotificationTypeEnum;
use App\Domain\Enums\AuditActionEnum;
use App\Domain\Enums\RoleEnum;
use PDOException;
use Throwable;

class ProcessTransferAction
{
    private UserServiceInterface $userService;
    private SecurityServiceInterface $security;

    public function __construct(UserServiceInterface $userService, SecurityServiceInterface $security)
    {
        $this->userService = $userService;
        $this->security = $security;
    }

    public function execute(\App\Application\DTOs\TransferRequestDTO $dto): array
    {
        $userId = $dto->userId;
        $to_account_no = $dto->toAccountNo;
        $amount = $dto->amount;
        $description = $dto->description;
        $txnPin = $dto->txnPin;

        $pdo = $this->userService->getDb();
        if (!validate_pin($pdo, $userId, $txnPin)) {
            throw new \App\Shared\Core\Exceptions\ApiException('Invalid transaction PIN', 401);
        }

        try {
            $pdo->beginTransaction();
            $account = $this->userService->getActiveAccount($userId);
            if (!$account) {
                throw new \App\Shared\Core\Exceptions\ResourceNotFoundException('Source account not found or inactive');
            }
            $source_id = $account['id'];
            $dest_id = $this->userService->getAccountIdByNumber($to_account_no);
            if (!$dest_id) {
                throw new \App\Shared\Core\Exceptions\ResourceNotFoundException('Destination account not found or inactive');
            }
            if ($source_id === $dest_id) {
                throw new \App\Shared\Core\Exceptions\BusinessRuleException('Cannot transfer to your own account');
            }

            // Idempotency: Prevent duplicate transfers (same amount to same destination within 2 minutes)
            $stmt = $pdo->prepare("SELECT id FROM transactions WHERE from_account_id = ? AND to_account_id = ? AND amount = ? AND created_at > (NOW() - INTERVAL 2 MINUTE)");
            $stmt->execute([$source_id, $dest_id, $amount]);
            if ($stmt->fetch()) {
                throw new \App\Shared\Core\Exceptions\BusinessRuleException('Duplicate transaction detected. Please wait 2 minutes before transferring the exact same amount to this recipient.');
            }

            $first_id = min($source_id, $dest_id);
            $second_id = max($source_id, $dest_id);
            $first = $this->userService->getAccountForUpdate($first_id);
            $second = $this->userService->getAccountForUpdate($second_id);
            $source = ($first['id'] == $source_id) ? $first : $second;
            $dest = ($first['id'] == $dest_id) ? $first : $second;
            if (!$source || !$dest) {
                throw new \App\Shared\Core\Exceptions\ApiException('Account error during locking', 500);
            }
            $dest['dest_user_id'] = $dest['customer_id'];
            if ($source['balance'] < $amount) {
                throw new \App\Shared\Core\Exceptions\BusinessRuleException('Insufficient balance');
            }
            $min_transfer = get_setting($pdo, 'min_transfer_amount', 10);
            if ($amount < $min_transfer) {
                throw new \App\Shared\Core\Exceptions\BusinessRuleException('Minimum transfer amount is ' . format_currency($min_transfer));
            }

            $today_total = (float)$this->userService->fetchTodayTransferTotal($source['id']);
            $daily_limit = $source['daily_limit'];
            if (($today_total + $amount) > $daily_limit) {
                $remaining = max(0, $daily_limit - $today_total);
                throw new \App\Shared\Core\Exceptions\BusinessRuleException("Daily transfer limit exceeded. Remaining limit: " . format_currency($remaining));
            }

            $new_source_balance = $source['balance'] - $amount;
            $this->userService->updateAccountBalance($source['id'], -$amount);
            $new_dest_balance = $dest['balance'] + $amount;
            $this->userService->updateAccountBalance($dest['id'], $amount);

            $transactionId = generate_transaction_id();
            $this->userService->insertTransaction($transactionId, $source['id'], $dest['id'], $amount, $new_source_balance, $new_dest_balance, $description);

            $safe_dest_name = html_encode($dest['full_name']);
            $safe_source_name = html_encode($source['full_name']);

            $pdo->commit();

            // Notifications after commit to isolate failure domain
            try {
                send_notification($pdo, $source['owner_user_id'], NotificationTypeEnum::TRANSACTION->value, 'Transfer Successful', "You transferred " . format_currency($amount) . " to {$safe_dest_name} ({$to_account_no}). Transaction ID: {$transactionId}");
                send_notification($pdo, $dest['owner_user_id'], NotificationTypeEnum::TRANSACTION->value, 'Money Received', "You received " . format_currency($amount) . " from {$safe_source_name} ({$source['account_no']}). Transaction ID: {$transactionId}");
                $this->security->writeAuditLog(AuditActionEnum::FUND_TRANSFER->value, $transactionId, 'transaction', $userId, RoleEnum::USER->value, "Transfer of " . format_currency($amount) . " from {$source['account_no']} to {$to_account_no}");
            } catch (\Throwable $notifErr) {
                \App\Shared\Core\Logging\Logger::getInstance()->warning('Failed to send transfer notifications: ', ['context' => $notifErr->getMessage()]);
            }

            return [
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'to_name' => $safe_dest_name,
                'to_account' => $to_account_no,
                'new_balance' => $new_source_balance
            ];
        } catch (\Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e instanceof \PDOException) {
                \App\Shared\Core\Logging\Logger::getInstance()->error('Transfer error: ', ['context' => $e->getMessage()]);
                throw new \App\Shared\Core\Exceptions\ApiException('Transfer failed. Please try again.', 500);
            }
            throw $e;
        }
    }
}
