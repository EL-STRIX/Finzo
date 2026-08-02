<?php

namespace App\Domain\Contracts\Repositories;

interface PdfRepositoryInterface
{
    public function getTransactionForReceipt(string $txnId): ?array;
    public function getUserAccounts(int $userId): array;
    public function getStaffStatementData(int $staffId): array;
    public function getStatementData(int $userId, string $fromDate, string $toDate): array;
}
