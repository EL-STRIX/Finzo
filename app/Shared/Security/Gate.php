<?php
namespace App\Shared\Security;

use App\Shared\Core\Exceptions\AuthorizationException;

class Gate
{
    private static array $policies = [
        'manage_admins' => ['super_admin'],
        'manage_staff'  => ['super_admin', 'admin'],
        'manage_users'  => ['super_admin', 'admin', 'manager', 'staff'],
        'approve_loans' => ['super_admin', 'admin', 'manager'],
        'view_reports'  => ['super_admin', 'admin', 'auditor'],
        'view_audit'    => ['super_admin', 'auditor'],
    ];

    public static function allows(string $ability): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        $role = $user['role'];

        // Super Admin bypass
        if ($role === 'super_admin') {
            return true;
        }

        if (isset(self::$policies[$ability]) && in_array($role, self::$policies[$ability])) {
            return true;
        }

        return false;
    }

    public static function authorize(string $ability): void
    {
        if (!self::allows($ability)) {
            throw new AuthorizationException("Access denied. Missing required permission: {$ability}");
        }
    }
}