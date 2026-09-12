<?php

namespace App\Domain\Contracts\Services;

interface LedgerServiceInterface
{
    public function getChartOfAccounts(): array;
    public function getJournalEntries(int $page, int $perPage, string $search = '', string $type = ''): array;
    public function getActivePeriod(): ?array;
    public function closePeriod(int $periodId, int $adminId): void;
    public function openPeriod(string $name, string $startDate, string $endDate, int $adminId): int;
    public function getLedgerSummary(): array;
    public function getJournalDetail(int $journalId): ?array;
    public function exportJournals(string $search = '', string $type = ''): string;
}
