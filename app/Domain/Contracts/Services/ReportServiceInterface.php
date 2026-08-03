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
}
