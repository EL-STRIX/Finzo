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
        $this->security = $security ?? new SecurityService();
    }

    public function generateFinancialReport(int $adminId, array $filters): array
    {
        $data = $this->repository->getFinancialSummary($filters);

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

    public function exportFinancialCsv(int $adminId, array $filters): string
    {
        $summary = $this->repository->getFinancialSummary($filters);
        $transactions = $this->repository->getFinancialTransactionsDetailed($filters, 1000);

        $fp = fopen('php://temp', 'r+');

        // Header Metadata
        fputcsv($fp, ['FINZO ENTERPRISE BANKING — FINANCIAL SUMMARY REPORT']);
        fputcsv($fp, ['Generated At', date('Y-m-d H:i:s')]);
        fputcsv($fp, ['Date Range', ($filters['date_from'] ?? 'All') . ' to ' . ($filters['date_to'] ?? 'All')]);
        fputcsv($fp, []);

        // Financial KPIs
        fputcsv($fp, ['METRIC', 'AMOUNT (INR)', 'TRANSACTION COUNT']);
        fputcsv($fp, ['Total Deposits', number_format($summary['total_deposits'], 2, '.', ''), $summary['deposit_count']]);
        fputcsv($fp, ['Total Withdrawals', number_format($summary['total_withdrawals'], 2, '.', ''), $summary['withdrawal_count']]);
        fputcsv($fp, ['Total Internal Transfers', number_format($summary['total_transfers'], 2, '.', ''), $summary['transfer_count']]);
        fputcsv($fp, ['Total Interest Accrued', number_format($summary['total_interest_paid'], 2, '.', ''), $summary['interest_count']]);
        fputcsv($fp, ['Net Cash Flow', number_format($summary['net_cash_flow'], 2, '.', ''), $summary['total_transactions']]);
        fputcsv($fp, []);

        // Detailed Transaction Ledger
        fputcsv($fp, ['TRANSACTION ID', 'TYPE', 'AMOUNT (INR)', 'STATUS', 'SOURCE ACCOUNT', 'DESTINATION ACCOUNT', 'DESCRIPTION', 'DATE & TIME']);
        foreach ($transactions as $tx) {
            fputcsv($fp, [
                $tx['txn_id'],
                strtoupper($tx['type']),
                number_format((float)$tx['amount'], 2, '.', ''),
                strtoupper($tx['status']),
                $tx['from_account'],
                $tx['to_account'],
                $tx['description'],
                $tx['created_at']
            ]);
        }

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        return $csvContent;
    }

    public function exportDemographicsCsv(int $adminId, array $filters): string
    {
        $demographics = $this->repository->getUserDemographics($filters);
        $users = $this->repository->getUserDemographicsDetailed($filters, 1000);

        $fp = fopen('php://temp', 'r+');

        // Header Metadata
        fputcsv($fp, ['FINZO ENTERPRISE BANKING — USER DEMOGRAPHICS & KYC REPORT']);
        fputcsv($fp, ['Generated At', date('Y-m-d H:i:s')]);
        fputcsv($fp, ['Date Range', ($filters['date_from'] ?? 'All') . ' to ' . ($filters['date_to'] ?? 'All')]);
        fputcsv($fp, []);

        // Overview Distribution
        fputcsv($fp, ['CATEGORY', 'DISTRIBUTION BREAKDOWN']);
        fputcsv($fp, ['Active Users', $demographics['user_statuses']['active'] ?? 0]);
        fputcsv($fp, ['Suspended Users', $demographics['user_statuses']['suspended'] ?? 0]);
        fputcsv($fp, ['Savings Accounts', $demographics['account_types']['savings'] ?? 0]);
        fputcsv($fp, ['Current Accounts', $demographics['account_types']['current'] ?? 0]);
        fputcsv($fp, ['KYC Approved', $demographics['kyc_statuses']['approved'] ?? 0]);
        fputcsv($fp, ['KYC Under Review', $demographics['kyc_statuses']['under_review'] ?? 0]);
        fputcsv($fp, ['KYC Rejected', $demographics['kyc_statuses']['rejected'] ?? 0]);
        fputcsv($fp, []);

        // Detailed Customer Directory
        fputcsv($fp, ['CUSTOMER ID', 'FULL NAME', 'EMAIL', 'PHONE', 'ACCOUNT NO', 'ACCOUNT TYPE', 'BALANCE (INR)', 'STATUS', 'KYC STATUS', 'BRANCH', 'REGISTERED DATE']);
        foreach ($users as $u) {
            fputcsv($fp, [
                $u['customer_id'],
                $u['full_name'],
                $u['email'],
                $u['phone'],
                $u['account_no'],
                strtoupper($u['account_type']),
                number_format((float)$u['balance'], 2, '.', ''),
                strtoupper($u['status']),
                strtoupper($u['kyc_status']),
                $u['branch_name'],
                $u['created_at']
            ]);
        }

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        return $csvContent;
    }

    public function getBranchesList(): array
    {
        return $this->repository->getBranchesList();
    }
}
