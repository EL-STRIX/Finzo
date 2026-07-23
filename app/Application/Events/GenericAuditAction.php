<?php

namespace App\Application\Events;

class GenericAuditAction extends AuditLogEvent
{
    public function __construct(string $actionType, string|int|null $entityId, string $entityType, string|int|null $actorId, string $actorRole, string $details)
    {
        parent::__construct($actionType, $entityId, $entityType, $actorId, $actorRole, $details);
    }
}
