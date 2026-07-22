<?php

namespace App\Application\Events;

class TransactionCompleted extends AuditLogEvent
{
    private array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
