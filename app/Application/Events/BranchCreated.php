<?php

namespace App\Application\Events;

class BranchCreated extends AuditLogEvent
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
