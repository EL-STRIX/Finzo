<?php

namespace Database;

use PDO;
use Exception;

class Migrator
{
    private PDO $db;
    private string $migrationsPath;
    private string $seedsPath;

    public function __construct(PDO $db, string $migrationsPath, string $seedsPath)
    {
        $this->db = $db;
        $this->migrationsPath = rtrim($migrationsPath, '/\\') . DIRECTORY_SEPARATOR;
        $this->seedsPath = rtrim($seedsPath, '/\\') . DIRECTORY_SEPARATOR;
        
        $this->initMigrationsTable();
    }

    private function initMigrationsTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function migrate(): void
    {
        $executed = $this->getExecutedMigrations();
        $files = $this->getMigrationFiles();

        $batch = $this->getNextBatchNumber();
        $migrated = false;

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (!in_array($name, $executed)) {
                echo "Migrating: {$name}\n";
                require_once $file;
                
                $className = $this->resolveClassName($name);
                $migration = new $className();
                $migration->up($this->db);

                $this->logMigration($name, $batch);
                echo "Migrated: {$name}\n";
                $migrated = true;
            }
        }

        if (!$migrated) {
            echo "Nothing to migrate.\n";
        }
    }

    public function rollback(): void
    {
        $lastBatch = $this->getLastBatchNumber();
        if ($lastBatch === 0) {
            echo "Nothing to rollback.\n";
            return;
        }

        $migrations = $this->getMigrationsByBatch($lastBatch);

        foreach ($migrations as $migrationRecord) {
            $name = $migrationRecord['migration'];
            echo "Rolling back: {$name}\n";
            
            $file = $this->migrationsPath . $name . '.php';
            if (file_exists($file)) {
                require_once $file;
                $className = $this->resolveClassName($name);
                $migration = new $className();
                $migration->down($this->db);
            } else {
                echo "Warning: Migration file {$file} not found.\n";
            }

            $this->removeMigration($name);
            echo "Rolled back: {$name}\n";
        }
    }

    public function seed(string $type = 'system'): void
    {
        echo "Running {$type} seeds...\n";
        $file = $this->seedsPath . ucfirst($type) . 'Seeder.php';
        
        if (!file_exists($file)) {
            echo "Seeder file not found: {$file}\n";
            return;
        }

        require_once $file;
        $className = ucfirst($type) . 'Seeder';
        $seeder = new $className();
        $seeder->run($this->db);
        echo "Seeding completed.\n";
    }

    public function reset(): void
    {
        echo "Resetting database...\n";
        $executed = array_reverse($this->getExecutedMigrations());

        foreach ($executed as $name) {
            echo "Rolling back: {$name}\n";
            $file = $this->migrationsPath . $name . '.php';
            if (file_exists($file)) {
                require_once $file;
                $className = $this->resolveClassName($name);
                $migration = new $className();
                $migration->down($this->db);
            }
            $this->removeMigration($name);
        }
        echo "Database reset successfully.\n";
    }

    private function getExecutedMigrations(): array
    {
        $stmt = $this->db->query("SELECT migration FROM migrations ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '*.php');
        sort($files);
        return $files;
    }

    private function getNextBatchNumber(): int
    {
        $stmt = $this->db->query("SELECT MAX(batch) FROM migrations");
        $batch = (int)$stmt->fetchColumn();
        return $batch + 1;
    }

    private function getLastBatchNumber(): int
    {
        $stmt = $this->db->query("SELECT MAX(batch) FROM migrations");
        return (int)$stmt->fetchColumn();
    }

    private function getMigrationsByBatch(int $batch): array
    {
        $stmt = $this->db->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$batch]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function logMigration(string $migration, int $batch): void
    {
        $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migration, $batch]);
    }

    private function removeMigration(string $migration): void
    {
        $stmt = $this->db->prepare("DELETE FROM migrations WHERE migration = ?");
        $stmt->execute([$migration]);
    }

    private function resolveClassName(string $filename): string
    {
        // Example: 2026_01_01_000000_create_users_table => CreateUsersTable
        $parts = explode('_', $filename);
        $parts = array_slice($parts, 4); // Remove Y_m_d_His
        $className = '';
        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }
        return $className;
    }
}
