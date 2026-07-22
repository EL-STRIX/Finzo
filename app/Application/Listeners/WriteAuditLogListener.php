<?php

namespace App\Application\Listeners;

use App\Shared\Security\SecurityService;

class WriteAuditLogListener
{
    protected SecurityService $security;

    public function __construct()
    {
        $this->security = new SecurityService();
    }

    public function handle(object $event): void
    {
        if (method_exists($event, 'getAuditData')) {
            $data = $event->getAuditData();
            $pdo = \App\Shared\Core\Database::getInstance();
            $this->security->writeAuditLog(
                $pdo,
                $data['action_type'],
                $data['entity_id'],
                $data['entity_type'],
                $data['actor_id'],
                $data['actor_role'],
                $data['details'] ?? ''
            );
        }
    }
}
