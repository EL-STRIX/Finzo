<?php

namespace App\Domain\Contracts\Repositories;

interface LedgerRepositoryInterface
{
    public function getChartOfAccounts(): array;
    public function getJournalEntries(int $limit, int $offset): array;
    public function getJournalEntriesCount(): int;
    public function getActiveAccountingPeriod(): ?array;
    public function closeAccountingPeriod(int $periodId, int $closedBy): bool;
}
