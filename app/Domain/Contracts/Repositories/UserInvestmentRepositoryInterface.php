<?php

namespace App\Domain\Contracts\Repositories;

interface UserInvestmentRepositoryInterface
{
    public function getFixedDeposits(int $userId): array;
    public function getFixedDepositById(int $fdId, int $userId);
    public function createFixedDeposit(array $data): int;
    public function updateFixedDepositStatus(int $fdId, string $status): void;
    public function updateAutoRenew(int $fdId, int $autoRenew): void;
}
