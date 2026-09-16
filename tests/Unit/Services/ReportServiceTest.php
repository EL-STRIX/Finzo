<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\ReportService;
use App\Domain\Contracts\Repositories\ReportRepositoryInterface;
use App\Shared\Security\SecurityService;

class ReportServiceTest extends TestCase
{
    private $repositoryMock;
    private $securityMock;
    private ReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = $this->createMock(ReportRepositoryInterface::class);
        $this->securityMock = $this->createMock(SecurityService::class);
        $this->service = new ReportService($this->repositoryMock, $this->securityMock);
    }

    public function testGenerateFinancialReport(): void
    {
        $summary = [
            'total_deposits' => 50000.0,
            'deposit_count' => 10,
            'total_withdrawals' => 20000.0,
            'withdrawal_count' => 5,
            'total_transfers' => 15000.0,
            'transfer_count' => 8,
            'total_interest_paid' => 120.0,
            'interest_count' => 1,
            'net_cash_flow' => 30000.0,
            'total_transactions' => 24
        ];

        $this->repositoryMock->expects($this->once())
            ->method('getFinancialSummary')
            ->with(['date_from' => '2026-01-01', 'date_to' => '2026-01-31'])
            ->willReturn($summary);

        $result = $this->service->generateFinancialReport(1, ['date_from' => '2026-01-01', 'date_to' => '2026-01-31']);

        $this->assertTrue($result['success']);
        $this->assertEquals('Financial Summary', $result['report']);
        $this->assertEquals($summary, $result['data']);
        $this->assertArrayHasKey('generated_at', $result);
    }

    public function testGenerateDemographicsReport(): void
    {
        $demographics = [
            'user_statuses' => [
                'active' => 120,
                'suspended' => 5
            ],
            'account_types' => [
                'savings' => 100,
                'current' => 25
            ],
            'kyc_statuses' => [
                'approved' => 110,
                'under_review' => 10,
                'rejected' => 5
            ]
        ];

        $this->repositoryMock->expects($this->once())
            ->method('getUserDemographics')
            ->willReturn($demographics);

        $result = $this->service->generateDemographicsReport(1, []);

        $this->assertTrue($result['success']);
        $this->assertEquals(125, $result['data']['total_users']);
        $this->assertEquals('User Demographics', $result['report']);
    }

    public function testExportFinancialCsv(): void
    {
        $summary = [
            'total_deposits' => 5000.0,
            'deposit_count' => 2,
            'total_withdrawals' => 1000.0,
            'withdrawal_count' => 1,
            'total_transfers' => 500.0,
            'transfer_count' => 1,
            'total_interest_paid' => 0.0,
            'interest_count' => 0,
            'net_cash_flow' => 4000.0,
            'total_transactions' => 4
        ];

        $transactions = [
            [
                'txn_id' => 'TXN10001',
                'type' => 'deposit',
                'amount' => 5000.0,
                'status' => 'completed',
                'from_account' => 'N/A',
                'to_account' => 'FNZ1001',
                'description' => 'Initial opening balance',
                'created_at' => '2026-01-01 10:00:00'
            ]
        ];

        $this->repositoryMock->method('getFinancialSummary')->willReturn($summary);
        $this->repositoryMock->method('getFinancialTransactionsDetailed')->willReturn($transactions);

        $csv = $this->service->exportFinancialCsv(1, []);

        $this->assertStringContainsString('FINZO ENTERPRISE BANKING — FINANCIAL SUMMARY REPORT', $csv);
        $this->assertStringContainsString('TXN10001', $csv);
        $this->assertStringContainsString('5000.00', $csv);
    }
}
