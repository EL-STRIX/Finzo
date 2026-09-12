<?php

namespace App\Application\Services;

use App\Domain\Contracts\Services\LedgerServiceInterface;
use App\Domain\Contracts\Repositories\LedgerRepositoryInterface;
use App\Shared\Core\Events\EventDispatcher;
use App\Application\Events\GenericAuditAction;
use App\Domain\Enums\AuditActionEnum;
use App\Domain\Enums\RoleEnum;

class LedgerService implements LedgerServiceInterface
{
    protected LedgerRepositoryInterface $repository;
    protected EventDispatcher $dispatcher;

    public function __construct(LedgerRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->dispatcher = EventDispatcher::getInstance();
    }

    public function getChartOfAccounts(): array
    {
        return $this->repository->getChartOfAccounts();
    }

    public function getJournalEntries(int $page, int $perPage, string $search = '', string $type = ''): array
    {
        $limit = max(10, min(100, $perPage));
        $offset = (max(1, $page) - 1) * $limit;

        $lines = $this->repository->getJournalEntries($limit, $offset, $search, $type);

        // Flatten journal lines into the row shape the admin ledger table renders:
        // transaction_id, created_at, type ('debit'|'credit'), amount, account_code, account_name, description.
        $rows = [];
        foreach ($lines as $line) {
            $isDebit = (float)$line['debit_amount'] > 0;
            $rows[] = [
                'line_id'        => (int)$line['line_id'],
                'journal_id'     => (int)$line['id'],
                'transaction_id' => $line['transaction_id'],
                'entry_date'     => $line['entry_date'],
                'created_at'     => $line['created_at'],
                'description'    => $line['description'],
                'reference_id'   => $line['reference'],
                'account_id'     => (int)$line['account_id'],
                'account_code'   => $line['account_code'],
                'account_name'   => $line['account_name'],
                'type'           => $isDebit ? 'debit' : 'credit',
                'amount'         => $isDebit ? (float)$line['debit_amount'] : (float)$line['credit_amount'],
                'debit_amount'   => (float)$line['debit_amount'],
                'credit_amount'  => (float)$line['credit_amount'],
            ];
        }

        $total = $this->repository->getJournalEntriesCount($search, $type);
        $pages = max(1, ceil($total / $limit));

        return [
            'data'         => $rows,
            'total'        => $total,
            'pages'        => $pages,
            'current_page' => $page,
            'per_page'     => $limit
        ];
    }

    public function getActivePeriod(): ?array
    {
        return $this->repository->getActiveAccountingPeriod();
    }

    public function closePeriod(int $periodId, int $adminId): void
    {
        $period = $this->repository->getActiveAccountingPeriod();
        if (!$period || (int)$period['id'] !== $periodId) {
            throw new \App\Shared\Core\Exceptions\BusinessRuleException('Invalid or inactive accounting period');
        }

        if (!$this->repository->closeAccountingPeriod($periodId, $adminId)) {
            throw new \Exception('Failed to close accounting period');
        }

        $periodName = $period['period_name'] ?? $period['name'] ?? 'Accounting Period';
        $this->dispatcher->dispatch(
            new GenericAuditAction(
                AuditActionEnum::SYSTEM_SETTINGS_UPDATED->value, 
                $periodId, 
                'accounting_periods', 
                $adminId, 
                RoleEnum::ADMIN->value, 
                "Closed accounting period: {$periodName}"
            )
        );
    }

    public function openPeriod(string $name, string $startDate, string $endDate, int $adminId): int
    {
        $active = $this->repository->getActiveAccountingPeriod();
        if ($active) {
            throw new \App\Shared\Core\Exceptions\BusinessRuleException('An accounting period is already open. Close it before opening a new period.');
        }

        if (empty(trim($name))) {
            throw new \App\Shared\Core\Exceptions\BusinessRuleException('Period name is required');
        }

        if (empty($startDate) || empty($endDate) || strtotime($startDate) > strtotime($endDate)) {
            throw new \App\Shared\Core\Exceptions\BusinessRuleException('Valid start and end dates are required (start date must be before end date)');
        }

        $periodId = $this->repository->openAccountingPeriod(trim($name), $startDate, $endDate);

        $this->dispatcher->dispatch(
            new GenericAuditAction(
                AuditActionEnum::SYSTEM_SETTINGS_UPDATED->value, 
                $periodId, 
                'accounting_periods', 
                $adminId, 
                RoleEnum::ADMIN->value, 
                "Opened new accounting period: {$name} ({$startDate} to {$endDate})"
            )
        );

        return $periodId;
    }

    public function getLedgerSummary(): array
    {
        return $this->repository->getLedgerSummary();
    }

    public function getJournalDetail(int $journalId): ?array
    {
        return $this->repository->getJournalDetail($journalId);
    }

    public function exportJournals(string $search = '', string $type = ''): string
    {
        $lines = $this->repository->getAllJournalLinesForExport($search, $type);
        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, [
            'Journal Number',
            'Posting Date',
            'Reference',
            'Description',
            'Source Module',
            'Account Code',
            'Account Name',
            'Category',
            'Type',
            'Debit (INR)',
            'Credit (INR)',
            'Created At'
        ], ',', '"', "\\");

        foreach ($lines as $line) {
            fputcsv($fp, [
                $line['journal_number'],
                $line['posting_date'],
                $line['reference'],
                $line['description'],
                $line['source_module'],
                $line['account_code'],
                $line['account_name'],
                ucfirst((string)$line['category']),
                $line['entry_type'],
                number_format((float)$line['debit_amount'], 2, '.', ''),
                number_format((float)$line['credit_amount'], 2, '.', ''),
                $line['created_at']
            ], ',', '"', "\\");
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);
        return $csv;
    }
}
