<?php

namespace Tests\Helpers;

use App\Shared\Core\Queue\Job;
use Exception;

class DummyFailingJob extends Job
{
    public int $executionCount = 0;

    public function __construct()
    {
        $this->retryAttempts = 2;
        $this->retryDelay = 0;
    }

    public function handle(): void
    {
        $this->executionCount++;
        throw new Exception("Intentional failure in test job");
    }
}
