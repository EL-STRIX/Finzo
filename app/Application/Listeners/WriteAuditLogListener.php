<?php

namespace App\Application\Listeners;

use App\Shared\Core\Queue\QueueManager;
use App\Application\Jobs\ProcessAuditLogJob;

class WriteAuditLogListener
{
    public function handle(object $event): void
    {
        if (method_exists($event, 'getAuditData')) {
            $data = $event->getAuditData();
            
            // Push to queue instead of executing synchronously
            $job = new ProcessAuditLogJob($data);
            QueueManager::getInstance()->dispatch($job);
        }
    }
}
