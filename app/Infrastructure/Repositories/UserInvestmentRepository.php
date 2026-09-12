<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\Repositories\UserInvestmentRepositoryInterface;

class UserInvestmentRepository extends BaseRepository implements UserInvestmentRepositoryInterface
{
    public function getFixedDeposits(int $userId): array
    {
        return $this->fetchAll("
            SELECT fd.*, a.account_no
            FROM fixed_deposits fd
            JOIN accounts a ON fd.account_id = a.id
            WHERE fd.user_id = ?
            ORDER BY fd.created_at DESC
        ", [$userId]);
    }

    public function getFixedDepositById(int $fdId, int $userId)
    {
        return $this->fetchOne("
            SELECT fd.*, a.account_no, a.balance, a.id as acc_id
            FROM fixed_deposits fd
            JOIN accounts a ON fd.account_id = a.id
            WHERE fd.id = ? AND fd.user_id = ?
        ", [$fdId, $userId]);
    }

    public function createFixedDeposit(array $data): int
    {
        $this->execute("
            INSERT INTO fixed_deposits (fd_number, user_id, account_id, deposit_amount, interest_rate, tenure_months, maturity_amount, maturity_date, auto_renew, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ", [
            $data['fd_number'],
            $data['user_id'],
            $data['account_id'],
            $data['deposit_amount'],
            $data['interest_rate'],
            $data['tenure_months'],
            $data['maturity_amount'],
            $data['maturity_date'],
            $data['auto_renew'] ?? 1
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateFixedDepositStatus(int $fdId, string $status): void
    {
        $this->execute("UPDATE fixed_deposits SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $fdId]);
    }

    public function updateAutoRenew(int $fdId, int $autoRenew): void
    {
        $this->execute("UPDATE fixed_deposits SET auto_renew = ?, updated_at = NOW() WHERE id = ?", [$autoRenew, $fdId]);
    }
}
