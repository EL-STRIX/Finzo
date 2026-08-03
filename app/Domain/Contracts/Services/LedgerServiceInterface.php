<?php

namespace App\Domain\Contracts\Services;

interface LedgerServiceInterface
{
    public function getChartOfAccounts(): array;
    public function getJournalEntries(int $page, int $perPage): array;
    public function getActivePeriod(): ?array;
    public function closePeriod(int $periodId, int $adminId): void;
}
