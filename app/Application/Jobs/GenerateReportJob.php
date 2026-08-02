<?php

namespace App\Application\Jobs;

use App\Shared\Core\Queue\JobInterface;

class GenerateReportJob implements JobInterface
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        // Implement GenerateReportJob logic
        \App\Shared\Core\Logging\Logger::getInstance()->info('Handling ' . self::class . ' with data: ' . json_encode($this->data));
    }
}
