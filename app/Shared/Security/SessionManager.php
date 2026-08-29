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
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.cookie_secure', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? '1' : '0');
            
            // Set max lifetime for garbage collection
            ini_set('session.gc_maxlifetime', (string)self::SESSION_LIFETIME);
            
            if (defined('STORAGE_PATH')) {
                ini_set('session.save_path', STORAGE_PATH . 'sessions');
            }

            $rawHost = $_SERVER['HTTP_HOST'] ?? '';
            $domain = preg_replace('/:\d+$/', '', $rawHost);
            if (empty($domain) || in_array(strtolower($domain), ['localhost', '127.0.0.1', '::1']) || filter_var($domain, FILTER_VALIDATE_IP)) {
                $domain = '';
            }

            session_set_cookie_params([
                'lifetime' => self::SESSION_LIFETIME,
                'path' => '/',
                'domain' => $domain,
                'secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();
        }

        // Auto-hydrate user details if session exists but user_name or user_customer_id is empty
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id']) && (empty($_SESSION['user_name']) || empty($_SESSION['user_customer_id']))) {
            try {
                $db = \App\Infrastructure\Database\DatabaseConnection::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT customer_id, full_name, email, phone, status FROM users WHERE id = ? AND is_deleted = 0");
                $stmt->execute([(int)$_SESSION['user_id']]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) {
                    if (empty($_SESSION['user_customer_id'])) $_SESSION['user_customer_id'] = $row['customer_id'];
                    if (empty($_SESSION['user_name'])) $_SESSION['user_name'] = $row['full_name'];
                    if (empty($_SESSION['user_email'])) $_SESSION['user_email'] = $row['email'];
                    if (empty($_SESSION['user_phone'])) $_SESSION['user_phone'] = $row['phone'];
                    if (empty($_SESSION['user_status'])) $_SESSION['user_status'] = $row['status'];
                }
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Regenerate session ID to prevent Session Fixation.
     * @param bool $deleteOldSession Whether to delete the old associated session file.
     */
    public static function regenerate(bool $deleteOldSession = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_regenerate_id($deleteOldSession);
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

    /**
     * Establish a session for a customer.
     */
    public static function establishUserSession(array $user, $account): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::regenerate(true);
        } else {
            self::start();
            self::regenerate(true);
        }

        $_SESSION = [];

        $_SESSION['user_id']          = (int)$user['id'];
        $_SESSION['user_customer_id'] = $user['customer_id'] ?? '';
        $_SESSION['user_name']        = !empty($user['full_name']) ? $user['full_name'] : (!empty($user['name']) ? $user['name'] : 'User');
        $_SESSION['user_email']       = $user['email'] ?? '';
        $_SESSION['user_phone']       = $user['phone'] ?? '';
        $_SESSION['user_status']      = $user['status'] ?? 'pending';
        $_SESSION['user_account_no']  = $account ? ($account['account_no'] ?? null) : null;
        $_SESSION['user_balance']     = $account ? ($account['balance'] ?? null) : null;
        $_SESSION['role']             = 'customer';
        $_SESSION['last_activity']    = time();
    }

    /**
     * Establish a session for an administrator.
     */
    public static function establishAdminSession(array $admin): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::regenerate(true);
        } else {
            self::start();
            self::regenerate(true);
        }

        $_SESSION = [];

        $_SESSION['admin_id']      = (int)$admin['id'];
        $_SESSION['admin_name']    = $admin['full_name'];
        $_SESSION['admin_email']   = $admin['email'];
        $_SESSION['admin_profile_photo']  = $admin['profile_photo'];
        $_SESSION['admin_role']    = $admin['admin_role'];
        $_SESSION['admin_display_id'] = $admin['admin_id'];
        $_SESSION['role']          = 'admin';
        $_SESSION['last_activity'] = time();
    }

    /**
     * Establish a session for a staff member.
     */
    public static function establishStaffSession(array $staff): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::regenerate(true);
        } else {
            self::start();
            self::regenerate(true);
        }

        $_SESSION = [];

        $_SESSION['staff_id']      = (int)$staff['id'];
        $_SESSION['staff_display_id'] = $staff['staff_id'];
        $_SESSION['staff_name']    = $staff['full_name'];
        $_SESSION['staff_email']   = $staff['email'];
        $_SESSION['staff_role']    = $staff['role'];
        $_SESSION['role']          = 'staff';
        $_SESSION['last_activity'] = time();
    }
}
