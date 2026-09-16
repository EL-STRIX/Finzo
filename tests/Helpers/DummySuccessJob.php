<?php

namespace Tests\Helpers;

use App\Shared\Core\Queue\Job;
use Exception;

class DummySuccessJob extends Job
{
    public bool $handled = false;

    public function handle(): void
    {
        $this->handled = true;
    }
}
