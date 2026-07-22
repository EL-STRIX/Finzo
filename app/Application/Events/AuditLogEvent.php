<?php

namespace App\Application\Events;

abstract class AuditLogEvent
{
    protected string $actionType;
    protected int $entityId;
    protected string $entityType;
    protected int $actorId;
    protected string $actorRole;
    protected string $details;

    public function __construct(string $actionType, int $entityId, string $entityType, int $actorId, string $actorRole, string $details)
    {
        $this->actionType = $actionType;
        $this->entityId = $entityId;
        $this->entityType = $entityType;
        $this->actorId = $actorId;
        $this->actorRole = $actorRole;
        $this->details = $details;
    }

    public function getAuditData(): array
    {
        return [
            'action_type' => $this->actionType,
            'entity_id' => $this->entityId,
            'entity_type' => $this->entityType,
            'actor_id' => $this->actorId,
            'actor_role' => $this->actorRole,
            'details' => $this->details,
        ];
    }
}
