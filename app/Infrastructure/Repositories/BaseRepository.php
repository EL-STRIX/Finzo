<?php

namespace App\Infrastructure\Repositories;

use App\Shared\Core\Database;
use PDO;

abstract class BaseRepository
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getPDO(): PDO
    {
        return $this->db;
    }

    public function getDb(): PDO
    {
        return $this->db;
    }

    /**
     * Helper for fetching a single row
     */
    protected function fetchOne(string $sql, array $params = [], ?string $modelClass = null)
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        if ($result && $modelClass && class_exists($modelClass)) {
            return new $modelClass($result);
        }
        return $result;
    }

    /**
     * Helper for fetching all rows
     */
    protected function fetchAll(string $sql, array $params = [], ?string $modelClass = null)
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        if ($results && $modelClass && class_exists($modelClass)) {
            return array_map(fn($row) => new $modelClass($row), $results);
        }
        return $results;
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->db->inTransaction();
    }

    /**
     * Helper for executing write operations
     */
    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Enforce business rules to prevent duplicate transactions
     */
    public function checkDuplicateTransaction(int $fromAccountId, float $amount, string $type): void
    {
        $sql = "SELECT COUNT(*) as dup_count 
                FROM transactions 
                WHERE from_account_id = :from_account_id 
                  AND amount = :amount 
                  AND type = :type 
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 5 SECOND)";
        
        $result = $this->fetchOne($sql, [
            ':from_account_id' => $fromAccountId,
            ':amount'          => $amount,
            ':type'            => $type
        ]);

        if ($result && $result['dup_count'] > 0) {
            throw new \App\Shared\Core\Exceptions\BusinessRuleException('Duplicate transaction detected. Please wait before retrying.');
        }
    }

    /**
     * Enforce business rules for daily limits
     */
    public function checkDailyTransferLimit(int $fromAccountId, float $amount, float $dailyLimit): void
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as daily_total 
                FROM transactions 
                WHERE from_account_id = :from_account_id 
                  AND type = 'transfer' 
                  AND status = 'completed'
                  AND DATE(created_at) = CURDATE()";

        $result = $this->fetchOne($sql, [':from_account_id' => $fromAccountId]);
        $dailyTotal = $result ? (float)$result['daily_total'] : 0.0;

        if (($dailyTotal + $amount) > $dailyLimit) {
            throw new \App\Shared\Core\Exceptions\BusinessRuleException('Transaction exceeds daily transfer limit.');
        }
    }
}
