<?php
namespace App\Shared\Security;

class SessionManager
{
    private const SESSION_LIFETIME = 1800; // 30 minutes

    /**
     * Start the session with enterprise-grade secure defaults.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            
            // Do not override in CLI/Testing environments unexpectedly
            if (defined('FINZO_TESTING') && FINZO_TESTING) {
                session_start();
                return;
            }

            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.cookie_secure', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? '1' : '0');
            
            // Set max lifetime for garbage collection
            ini_set('session.gc_maxlifetime', (string)self::SESSION_LIFETIME);
            
            if (defined('STORAGE_PATH')) {
                ini_set('session.save_path', STORAGE_PATH . 'sessions');
            }

            session_set_cookie_params([
                'lifetime' => self::SESSION_LIFETIME,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'] ?? '',
                'secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            session_start();
        }
    }

    /**
     * Regenerate session ID to prevent Session Fixation.
     * @param bool $deleteOldSession Whether to delete the old associated session file.
     */
    public static function regenerate(bool $deleteOldSession = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id($deleteOldSession);
        }
    }

    /**
     * Completely destroy the session and invalidate the cookie.
     */
    public static function destroy(): void
    {
        $_SESSION = [];
        
        if (session_status() === PHP_SESSION_ACTIVE) {
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
