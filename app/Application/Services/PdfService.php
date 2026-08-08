<?php
namespace App\Application\Services;

use App\Infrastructure\Repositories\PdfRepository;

class PdfService implements \App\Domain\Contracts\Services\PdfServiceInterface
{
    private PdfRepository $repository;
    /**
     *   construct.
     *
     * @param \App\Domain\Contracts\Repositories\PdfRepositoryInterface $repository
     * @param ?\App\Domain\Contracts\Security\SecurityServiceInterface $security
     */
    public function __construct(\App\Domain\Contracts\Repositories\PdfRepositoryInterface $repository, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->repository = $repository;
    }
    /**
     * Get Receipt Data.
     *
     * @param string $txnId
     * @param ?string $role
     * @param ?int $userId
     * @return ?array
     */
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
    /**
     * Get Staff Statement.
     *
     * @param int $staffId
     * @return array
     */
    public function getStaffStatement(int $staffId): array
    {
        return $this->repository->getStaffStatementData($staffId);
    }
    /**
     * Get Statement.
     *
     * @param int $userId
     * @param string $fromDate
     * @param string $toDate
     * @return array
     */
    public function getStatement(int $userId, string $fromDate, string $toDate): array
    {
        return $this->repository->getStatementData($userId, $fromDate, $toDate);
    }
}
