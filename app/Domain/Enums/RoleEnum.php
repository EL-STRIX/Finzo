<?php

namespace App\Domain\Enums;

enum RoleEnum: string
{
    case USER = 'user';
    case STAFF = 'staff';
    case ADMIN = 'admin';
    case SYSTEM = 'system';
}
