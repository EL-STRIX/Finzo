<?php

namespace App\Domain\Contracts\Repositories;

interface LedgerRepositoryInterface
{
    public function getChartOfAccounts(): array;
    public function getJournalEntries(int $limit, int $offset, string $search = '', string $type = ''): array;
    public function getJournalEntriesCount(string $search = '', string $type = ''): int;
    public function getActiveAccountingPeriod(): ?array;
    public function closeAccountingPeriod(int $periodId, int $closedBy): bool;
    public function openAccountingPeriod(string $name, string $startDate, string $endDate): int;
    public function getLedgerSummary(): array;
    public function getJournalDetail(int $journalId): ?array;
    public function getAllJournalLinesForExport(string $search = '', string $type = ''): array;
}
