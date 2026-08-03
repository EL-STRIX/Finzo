<?php

namespace App\Application\Services;

use App\Domain\Contracts\Services\ReportServiceInterface;
use App\Domain\Contracts\Repositories\ReportRepositoryInterface;
use App\Shared\Security\SecurityService;

class ReportService implements ReportServiceInterface
{
    protected ReportRepositoryInterface $repository;
    protected ?SecurityService $security;

    public function __construct(ReportRepositoryInterface $repository, ?SecurityService $security = null)
    {
        $this->repository = $repository;
        $this->security = $security;
    }

    public function generateFinancialReport(int $adminId, array $filters): array
    {
        if ($this->security) {
            // Can add specific permission check here if needed, e.g., 'view_financial_reports'
            // $this->security->checkAdminPermission($adminId, 'view_reports');
        }

        $data = $this->repository->getFinancialSummary($filters);

        // Calculate net cash flow (Deposits - Withdrawals)
        $data['net_cash_flow'] = $data['total_deposits'] - $data['total_withdrawals'];

        return [
            'success' => true,
            'report' => 'Financial Summary',
            'filters' => $filters,
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $data
        ];
    }

    public function generateDemographicsReport(int $adminId, array $filters): array
    {
        if ($this->security) {
            // $this->security->checkAdminPermission($adminId, 'view_reports');
        }

        $data = $this->repository->getUserDemographics($filters);

        $totalUsers = array_sum($data['user_statuses']);
        $data['total_users'] = $totalUsers;

        return [
            'success' => true,
            'report' => 'User Demographics',
            'filters' => $filters,
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $data
        ];
    }
}
