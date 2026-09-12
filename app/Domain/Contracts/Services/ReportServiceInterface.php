<?php

namespace App\Domain\Contracts\Services;

interface ReportServiceInterface
{
    /**
     * Generate financial summary report.
     *
     * @param int $adminId
     * @param array $filters
     * @return array
     */
    public function generateFinancialReport(int $adminId, array $filters): array;

    /**
     * Generate user demographics report.
     *
     * @param int $adminId
     * @param array $filters
     * @return array
     */
    public function generateDemographicsReport(int $adminId, array $filters): array;

    /**
     * Export financial summary and transaction ledger to CSV string.
     *
     * @param int $adminId
     * @param array $filters
     * @return string
     */
    public function exportFinancialCsv(int $adminId, array $filters): string;

    /**
     * Export user demographics and KYC records to CSV string.
     *
     * @param int $adminId
     * @param array $filters
     * @return string
     */
    public function exportDemographicsCsv(int $adminId, array $filters): string;

    /**
     * Get list of active branches for filter selection.
     *
     * @return array
     */
    public function getBranchesList(): array;
}
