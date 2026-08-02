<?php

namespace App\Domain\Enums;

enum AccountStatusEnum: string
{
    case ACTIVE = 'active';
    case PENDING = 'pending';
    case SUSPENDED = 'suspended';
    case REJECTED = 'rejected';
    case BLOCKED = 'blocked';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REVERSED = 'reversed';
}
