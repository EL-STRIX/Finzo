<?php

namespace App\Domain\Contracts\Services;

interface PdfServiceInterface
{
    public function getReceiptData(string $txnId, ?string $role, ?int $userId): ?array;
    public function getStaffStatement(int $staffId): array;
    public function getStatement(int $userId, string $fromDate, string $toDate): array;
}
