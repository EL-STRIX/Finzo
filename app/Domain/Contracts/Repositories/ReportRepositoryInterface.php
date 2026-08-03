<?php

namespace App\Domain\Contracts\Repositories;

interface ReportRepositoryInterface
{
    /**
     * Get financial summary report data based on filters.
     *
     * @param array $filters
     * @return array
     */
    public function getFinancialSummary(array $filters): array;

    /**
     * Get user demographics report data based on filters.
     *
     * @param array $filters
     * @return array
     */
    public function getUserDemographics(array $filters): array;
}
