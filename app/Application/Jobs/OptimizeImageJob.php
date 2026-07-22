<?php

namespace App\Application\Jobs;

use App\Shared\Core\Queue\JobInterface;

class OptimizeImageJob implements JobInterface
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        // Implement OptimizeImageJob logic
        error_log('Handling ' . self::class . ' with data: ' . json_encode($this->data));
    }
}
