<?php

use App\Shared\Core\Events\EventDispatcher;
use App\Application\Events\GenericAuditAction;
use App\Application\Listeners\WriteAuditLogListener;

$dispatcher = EventDispatcher::getInstance();

$dispatcher->listen(GenericAuditAction::class, [new WriteAuditLogListener(), 'handle']);
