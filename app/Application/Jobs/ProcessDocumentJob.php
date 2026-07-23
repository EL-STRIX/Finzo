<?php

namespace App\Application\Jobs;

use App\Shared\Core\Queue\JobInterface;

class ProcessDocumentJob implements JobInterface
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        // Implement ProcessDocumentJob logic
        error_log('Handling ' . self::class . ' with data: ' . json_encode($this->data));
    }
}
