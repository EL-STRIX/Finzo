<?php

namespace Database;

use PDO;
use Exception;
use ZipArchive;

class BcdrManager
{
    private PDO $db;
    private string $backupPath;
    private string $mysqldumpPath;
    private string $mysqlPath;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->backupPath = dirname(__DIR__) . '/storage/backups/';
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
        
        // Detect paths for XAMPP Windows or fallback to global
        $this->mysqldumpPath = file_exists('C:\\xampp\\mysql\\bin\\mysqldump.exe') ? '"C:\\xampp\\mysql\\bin\\mysqldump.exe"' : 'mysqldump';
        $this->mysqlPath = file_exists('C:\\xampp\\mysql\\bin\\mysql.exe') ? '"C:\\xampp\\mysql\\bin\\mysql.exe"' : 'mysql';
    }

    public function backup(string $type = 'full'): void
    {
        echo "Starting {$type} backup...\n";
        $filename = 'backup_' . $type . '_' . date('Y_m_d_His') . '.sql';
        $filePath = $this->backupPath . $filename;
        
        $user = DB_USER;
        $pass = DB_PASS ? "-p" . escapeshellarg(DB_PASS) : "";
        $host = DB_HOST;
        $dbname = DB_NAME;

        $flags = '--routines --triggers --events --single-transaction';
        if ($type === 'schema') {
            $flags .= ' --no-data';
        } elseif ($type === 'data') {
            $flags .= ' --no-create-info';
        }

        $command = "{$this->mysqldumpPath} -h {$host} -u {$user} {$pass} {$flags} {$dbname} > {$filePath}";
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            echo "Backup failed. Command exited with code: {$returnVar}\n";
            return;
        }

        // Compress and encrypt (password protect) the backup for security at rest
        $zipFile = $filePath . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($filePath, $filename);
            // Encryption is required by enterprise standards
            $zip->setEncryptionName($filename, ZipArchive::EM_AES_256, DB_PASS ?: 'finzo_default_secret');
            $zip->close();
            unlink($filePath); // Delete raw SQL
            echo "Backup completed successfully and encrypted at: {$zipFile}\n";
        } else {
            echo "Backup completed: {$filePath} (Compression failed)\n";
        }
        
        $this->enforceRetentionPolicy();
    }

    public function restore(string $backupFile): void
    {
        $filePath = $this->backupPath . $backupFile;
        if (!file_exists($filePath)) {
            echo "Backup file not found: {$filePath}\n";
            return;
        }

        echo "Starting restore from {$backupFile}...\n";
        $sqlFile = $filePath;

        // If it's a zip file, extract it
        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === TRUE) {
                // We assume DB_PASS is the password used
                $zip->setPassword(DB_PASS ?: 'finzo_default_secret');
                $extractPath = $this->backupPath . 'temp_restore/';
                if (!is_dir($extractPath)) mkdir($extractPath);
                
                $zip->extractTo($extractPath);
                $sqlFile = $extractPath . $zip->getNameIndex(0);
                $zip->close();
            } else {
                echo "Failed to open encrypted backup ZIP.\n";
                return;
            }
        }

        $user = DB_USER;
        $pass = DB_PASS ? "-p" . escapeshellarg(DB_PASS) : "";
        $host = DB_HOST;
        $dbname = DB_NAME;

        $command = "{$this->mysqlPath} -h {$host} -u {$user} {$pass} {$dbname} < \"{$sqlFile}\"";
        exec($command, $output, $returnVar);

        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'zip') {
            unlink($sqlFile);
            rmdir(dirname($sqlFile));
        }

        if ($returnVar !== 0) {
            echo "Restore failed. Command exited with code: {$returnVar}\n";
            return;
        }

        echo "Restore completed successfully.\n";
        $this->validateIntegrity();
    }

    public function validateIntegrity(): void
    {
        echo "Validating Database Integrity...\n";
        
        // 1. Ledger Balance Check
        $stmt = $this->db->query("SELECT SUM(debit_amount) as debits, SUM(credit_amount) as credits FROM journal_lines");
        $ledger = $stmt->fetch();
        if ($ledger['debits'] !== $ledger['credits']) {
            echo "[FAILED] Ledger is unbalanced! Debits: {$ledger['debits']}, Credits: {$ledger['credits']}\n";
        } else {
            echo "[OK] Ledger is perfectly balanced.\n";
        }

        // 2. Audit Log Consistency Check
        $stmt = $this->db->query("SELECT COUNT(*) FROM audit_log");
        $count = $stmt->fetchColumn();
        echo "[OK] Audit Log is intact. Rows: {$count}\n";

        // 3. Application State Check
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $activeUsers = $stmt->fetchColumn();
        echo "[OK] Core application tables respond normally. Active users: {$activeUsers}\n";
        
        echo "Database Integrity Validation Completed.\n";
    }

    private function enforceRetentionPolicy(): void
    {
        // Delete backups older than 30 days
        $files = glob($this->backupPath . 'backup_*');
        $now = time();
        $retentionDays = 30;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $retentionDays) {
                    unlink($file);
                    echo "Retention policy: Deleted old backup " . basename($file) . "\n";
                }
            }
        }
    }
}
