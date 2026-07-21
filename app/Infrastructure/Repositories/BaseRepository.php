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

    /**
     * Helper for fetching a single row
     */
    protected function fetchOne(string $sql, array $params = [], string $modelClass = null)
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
    protected function fetchAll(string $sql, array $params = [], string $modelClass = null)
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
}
