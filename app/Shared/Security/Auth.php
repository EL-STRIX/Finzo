<?php
namespace App\Shared\Security;

use App\Shared\Core\Exceptions\AuthenticationException;
use App\Shared\Core\Exceptions\AuthorizationException;

class Auth
{
    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function user(): ?array
    {
        // Extension Point for JWT / API Authentication
        $tokenUser = self::resolveBearerToken();
        if ($tokenUser) {
            return $tokenUser;
        }

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

    private static function resolveBearerToken(): ?array
    {
        $headers = [];
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            foreach ($_SERVER as $key => $value) {
                if (str_starts_with($key, 'HTTP_')) {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $value;
                }
            }
        }
        
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (str_starts_with($authHeader, 'Bearer ')) {
            // $token = substr($authHeader, 7);
            // Future extension point for JWT decoding/validation
            throw new AuthenticationException('JWT authentication is not fully configured yet.');
        }

        return null;
    }

    public static function requireAuth(): array
    {
        $user = self::user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated. Please log in.');
        }
        
        // Skip session activity check if authenticated via token
        if (!isset($user['is_token'])) {
            // Session Expiration Check
            $timeout = 1800; // 30 minutes
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
                self::logout();
                throw new AuthenticationException('Session expired. Please log in again.');
            }
            $_SESSION['last_activity'] = time();
        }
        
        return $user;
    }

    public static function requireAdmin(): array
    {
        $user = self::requireAuth();
        if ($user['type'] !== 'admin' && $user['type'] !== 'staff') {
            throw new AuthorizationException('Access denied. Administrator or Staff privileges required.');
        }
        return $user;
    }

    public static function requireStrictAdmin(): array
    {
        $user = self::requireAuth();
        if ($user['type'] !== 'admin') {
            throw new AuthorizationException('Access denied. Strict Administrator privileges required.');
        }
        return $user;
    }

    public static function requireStrictStaff(): array
    {
        $user = self::requireAuth();
        if ($user['type'] !== 'staff') {
            throw new AuthorizationException('Access denied. Staff privileges required.');
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
        \App\Shared\Security\SessionManager::destroy();
    }
}