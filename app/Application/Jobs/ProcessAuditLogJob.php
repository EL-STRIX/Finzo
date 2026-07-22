<?php

namespace App\Application\Jobs;

use App\Shared\Core\Queue\Job;
use App\Shared\Security\SecurityService;
use App\Shared\Core\Database;

class ProcessAuditLogJob extends Job
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        $security = new SecurityService();
        $pdo = Database::getInstance();
        
        $security->writeAuditLog(
            $pdo,
            $this->data['action_type'],
            $this->data['entity_id'],
            $this->data['entity_type'],
            $this->data['actor_id'],
            $this->data['actor_role'],
            $this->data['details'] ?? ''
        );
    }
}
