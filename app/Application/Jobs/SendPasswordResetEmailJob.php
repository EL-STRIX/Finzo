<?php

namespace App\Application\Jobs;

use App\Shared\Core\Queue\JobInterface;

class SendPasswordResetEmailJob implements JobInterface
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        // Implement SendPasswordResetEmailJob logic
        error_log('Handling ' . self::class . ' with data: ' . json_encode($this->data));
    }
}
