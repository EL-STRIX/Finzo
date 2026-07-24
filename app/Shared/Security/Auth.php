<?php
namespace App\Shared\Security;

use App\Shared\Core\Exceptions\AuthenticationException;
use App\Shared\Core\Exceptions\AuthorizationException;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']) || isset($_SESSION['staff_id']);
    }

    public static function user(): ?array
    {
        if (isset($_SESSION['admin_id'])) {
            return ['id' => $_SESSION['admin_id'], 'type' => 'admin', 'role' => $_SESSION['admin_role'] ?? 'admin'];
        }
        if (isset($_SESSION['staff_id'])) {
            return ['id' => $_SESSION['staff_id'], 'type' => 'staff', 'role' => $_SESSION['staff_role'] ?? 'staff'];
        }
        if (isset($_SESSION['user_id'])) {
            return ['id' => $_SESSION['user_id'], 'type' => 'customer', 'role' => 'customer'];
        }
        return null;
    }

    public static function requireAuth(): array
    {
        $user = self::user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated. Please log in.');
        }
        
        // Session Expiration Check
        $timeout = 1800; // 30 minutes
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            self::logout();
            throw new AuthenticationException('Session expired. Please log in again.');
        }
        $_SESSION['last_activity'] = time();
        
        return $user;
    }

    public static function requireAdmin(): array
    {
        $user = self::requireAuth();
        if ($user['type'] !== 'admin' && $user['type'] !== 'staff') {
            throw new AuthorizationException('Access denied. Administrator privileges required.');
        }
        return $user;
    }

    public static function requireCustomer(): array
    {
        $user = self::requireAuth();
        if ($user['type'] !== 'customer') {
            throw new AuthorizationException('Access denied. Customer privileges required.');
        }
        return $user;
    }

    public static function logout(): void
    {
        if (function_exists('destroy_session')) {
            destroy_session();
        } else {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_unset();
            session_destroy();
        }
    }
}