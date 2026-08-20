<?php

namespace App\Shared\Core\Queue;

use App\Shared\Core\Exceptions\ApiException;

class QueueManager
{
    private static ?QueueManager $instance = null;
    private array $failedJobs = [];

    private function __construct() {}

    public static function getInstance(): QueueManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function dispatch(JobInterface $job, string $queue = 'default'): void
    {
        $connection = defined('QUEUE_CONNECTION') ? QUEUE_CONNECTION : 'sync';
        if ($connection === 'sync') {
            $this->processJobSync($job);
            return;
        }

        try {
            $pdo = \App\Shared\Core\Database::getInstance();
            $stmt = $pdo->prepare("INSERT INTO jobs (queue, payload, attempts, available_at, created_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $queue,
                serialize($job),
                0,
                time(),
                time()
            ]);
        } catch (\PDOException $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Failed to dispatch job: ', ['context' => $e->getMessage()]);
            // Fallback to synchronous if DB fails to queue
            $this->processJobSync($job);
        }
    }

    /**
     * Used by the queue worker daemon
     */
    public function processJobSync(JobInterface $job): void
    {
        $maxAttempts = $job->getRetryAttempts();
        
        while ($job->getAttempts() < $maxAttempts) {
            try {
                $job->incrementAttempts();
                $job->handle();
                return; // Success
            } catch (\Exception $e) {
                if ($job->getAttempts() >= $maxAttempts) {
                    $this->failJob($job, $e);
                    \App\Shared\Core\Logging\Logger::getInstance()->error('Job failed permanently: ', ['context' => get_class($job) . " - " . $e->getMessage()]);
                    return;
                }
                
                if ($job->getRetryDelay() > 0) {
                    sleep($job->getRetryDelay());
                }
            }
        }
    }

    public function failJob(JobInterface $job, \Exception $e): void
    {
        try {
            $pdo = \App\Shared\Core\Database::getInstance();
            $stmt = $pdo->prepare("INSERT INTO failed_jobs (payload, exception) VALUES (?, ?)");
            $stmt->execute([serialize($job), $e->getMessage()]);
        } catch (\PDOException $dbException) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Failed to persist failed job to database: ', ['context' => $dbException->getMessage()]);
        }
    }
}
