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

    /**
     * Get detailed financial transactions for export.
     *
     * @param array $filters
     * @param int $limit
     * @return array
     */
    public function getFinancialTransactionsDetailed(array $filters, int $limit = 1000): array;

    /**
     * Get detailed user demographics records for export.
     *
     * @param array $filters
     * @param int $limit
     * @return array
     */
    public function getUserDemographicsDetailed(array $filters, int $limit = 1000): array;

    /**
     * Get list of active branches.
     *
     * @return array
     */
    public function getBranchesList(): array;
}
