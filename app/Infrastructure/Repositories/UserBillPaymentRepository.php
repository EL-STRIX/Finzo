<?php

namespace App\Infrastructure\Repositories;

class UserBillPaymentRepository extends BaseRepository
{
    public function getSavedBillers(int $userId): array
    {
        return $this->fetchAll("
            SELECT * FROM user_billers WHERE user_id = ? ORDER BY created_at DESC
        ", [$userId]);
    }

    public function createBiller(int $userId, string $category, string $billerName, string $consumerNumber): int
    {
        $this->execute("
            INSERT INTO user_billers (user_id, category, biller_name, consumer_number, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ", [$userId, $category, $billerName, $consumerNumber]);
        return (int)$this->db->lastInsertId();
    }

    public function createBillPayment(int $userId, string $billerName, string $category, string $consumerNumber, float $amount, string $referenceNo): int
    {
        $this->execute("
            INSERT INTO bill_payments (user_id, biller_name, category, consumer_number, amount, reference_no, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'completed', NOW())
        ", [$userId, $billerName, $category, $consumerNumber, $amount, $referenceNo]);
        return (int)$this->db->lastInsertId();
    }

    public function getPaymentHistory(int $userId): array
    {
        return $this->fetchAll("
            SELECT * FROM bill_payments WHERE user_id = ? ORDER BY created_at DESC
        ", [$userId]);
    }
}
