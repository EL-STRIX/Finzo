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

    public function dispatch(JobInterface $job): void
    {
        // For standard environment without a daemon worker, we execute synchronously to guarantee execution.
        // In an enterprise environment, this would serialize the job and push to Redis/DB.
        $this->processJob($job);
    }

    private function processJob(JobInterface $job): void
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
                    // Do not bubble up to crash the main thread if it's a background queue
                    error_log("Job failed permanently: " . get_class($job) . " - " . $e->getMessage());
                    return;
                }
                
                // Sleep for retry delay
                if ($job->getRetryDelay() > 0) {
                    sleep($job->getRetryDelay());
                }
            }
        }
    }

    private function failJob(JobInterface $job, \Exception $e): void
    {
        $this->failedJobs[] = [
            'job' => get_class($job),
            'exception' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s'),
            'attempts' => $job->getAttempts()
        ];
        
        // Write to failed_jobs DB table (placeholder for architecture)
        // $pdo = \App\Shared\Core\Database::getInstance();
        // $stmt = $pdo->prepare("INSERT INTO failed_jobs (payload, exception, failed_at) VALUES (?, ?, NOW())");
        // $stmt->execute([serialize($job), $e->getMessage()]);
    }

    public function getFailedJobs(): array
    {
        return $this->failedJobs;
    }
}
