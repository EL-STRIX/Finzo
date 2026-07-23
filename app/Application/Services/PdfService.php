<?php
namespace App\Application\Services;

use App\Infrastructure\Repositories\PdfRepository;

class PdfService
{
    private PdfRepository $repository;

    public function __construct(PdfRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getReceiptData(string $txnId, ?string $role, ?int $userId): ?array
    {
        $txn = $this->repository->getTransactionForReceipt($txnId);
        if (!$txn) return null;

        if ($role === 'customer' && $userId) {
            $user_accounts = $this->repository->getUserAccounts($userId);
            if (!in_array($txn['from_account_id'], $user_accounts) && !in_array($txn['to_account_id'], $user_accounts)) {
                return null;
            }
        }
        return $txn;
    }
    
    public function getStaffStatement(int $staffId): array
    {
        return $this->repository->getStaffStatementData($staffId);
    }
    
    public function getStatement(int $userId, string $fromDate, string $toDate): array
    {
        return $this->repository->getStatementData($userId, $fromDate, $toDate);
    }
}
