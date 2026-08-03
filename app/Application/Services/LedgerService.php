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

    public function getJournalEntries(int $page, int $perPage): array
    {
        $limit = max(10, min(100, $perPage));
        $offset = (max(1, $page) - 1) * $limit;

        $lines = $this->repository->getJournalEntries($limit, $offset);
        
        // Group lines by journal entry ID
        $entries = [];
        foreach ($lines as $line) {
            $jId = $line['id'];
            if (!isset($entries[$jId])) {
                $entries[$jId] = [
                    'id' => $line['id'],
                    'transaction_id' => $line['transaction_id'],
                    'entry_date' => $line['entry_date'],
                    'description' => $line['description'],
                    'reference_id' => $line['reference_id'],
                    'created_at' => $line['created_at'],
                    'lines' => []
                ];
            }
            $entries[$jId]['lines'][] = [
                'line_id' => $line['line_id'],
                'account_id' => $line['account_id'],
                'account_code' => $line['account_code'],
                'account_name' => $line['account_name'],
                'debit_amount' => (float)$line['debit_amount'],
                'credit_amount' => (float)$line['credit_amount']
            ];
        }

        $total = $this->repository->getJournalEntriesCount();
        $pages = max(1, ceil($total / $limit));

        return [
            'entries' => array_values($entries),
            'total' => $total,
            'pages' => $pages,
            'current_page' => $page,
            'per_page' => $limit
        ];
    }

    public function getActivePeriod(): ?array
    {
        return $this->repository->getActiveAccountingPeriod();
    }

    public function closePeriod(int $periodId, int $adminId): void
    {
        $period = $this->repository->getActiveAccountingPeriod();
        if (!$period || $period['id'] !== $periodId) {
            throw new \App\Shared\Core\Exceptions\BusinessRuleException('Invalid or inactive accounting period');
        }

        if (!$this->repository->closeAccountingPeriod($periodId, $adminId)) {
            throw new \Exception('Failed to close accounting period');
        }

        $this->dispatcher->dispatch(
            new GenericAuditAction(
                AuditActionEnum::SYSTEM_SETTINGS_UPDATE->value ?? 'SYSTEM_SETTINGS_UPDATE', 
                $periodId, 
                'accounting_periods', 
                $adminId, 
                RoleEnum::ADMIN->value, 
                "Closed accounting period: {$period['period_name']}"
            )
        );
    }
}
