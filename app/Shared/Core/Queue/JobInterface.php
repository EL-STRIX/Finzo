<?php

namespace App\Shared\Core\Queue;

interface JobInterface
{
    public function handle(): void;
    public function getRetryAttempts(): int;
    public function getRetryDelay(): int;
}
