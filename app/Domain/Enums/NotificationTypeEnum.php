<?php

namespace App\Domain\Enums;

enum NotificationTypeEnum: string
{
    case SYSTEM = 'system';
    case SECURITY = 'security';
    case TRANSACTION = 'transaction';
    case ALERT = 'alert';
}
