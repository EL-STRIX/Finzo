<?php

namespace App\Shared\Core\Queue;

abstract class Job implements JobInterface
{
    protected int $retryAttempts = 3;
    protected int $retryDelay = 5; // seconds
    protected int $attempts = 0;

    public function getRetryAttempts(): int
    {
        return $this->retryAttempts;
    }

    public function getRetryDelay(): int
    {
        return $this->retryDelay;
    }

    public function incrementAttempts(): void
    {
        $this->attempts++;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }
}
