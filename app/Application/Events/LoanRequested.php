<?php

namespace App\Application\Events;

class LoanRequested extends AuditLogEvent
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
