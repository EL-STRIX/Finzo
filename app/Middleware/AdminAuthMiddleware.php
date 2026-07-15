<?php
namespace App\Middleware;

use App\Core\Exceptions\ApiException;
use App\Core\Database;

class AdminAuthMiddleware {
    public function handle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            throw new ApiException('Authentication required. Please log in.', 401, ['redirect' => '/views/auth/admin-login.html']);
        }

        if (!isset($_SESSION['admin_id'])) {
            destroy_session();
            throw new ApiException('Session expired. Please log in again.', 401, ['redirect' => '/views/auth/admin-login.html']);
        }

        if (is_session_expired()) {
            destroy_session();
            throw new ApiException('Session expired. Please log in again.', 401, ['redirect' => '/views/auth/admin-login.html']);
        }

        $pdo = Database::getInstance();

        if (!isset($_SESSION['admin_role'])) {
            $role_stmt = $pdo->prepare("SELECT admin_role FROM admin_users WHERE id = ?");
            $role_stmt->execute([$_SESSION['admin_id']]);
            $role = $role_stmt->fetchColumn();
            $_SESSION['admin_role'] = $role ?: 'admin';
        }

        $lock_stmt = $pdo->prepare("SELECT account_locked, auto_unlock_at, status, email FROM admin_users WHERE id = ?");
        $lock_stmt->execute([$_SESSION['admin_id']]);
        $admin_status_row = $lock_stmt->fetch(\PDO::FETCH_ASSOC);

        if ($admin_status_row) {
            if ($admin_status_row['account_locked'] == 1) {
                if ($admin_status_row['auto_unlock_at'] && strtotime($admin_status_row['auto_unlock_at']) <= time()) {
                    $pdo->prepare("
                        UPDATE admin_users 
                        SET account_locked = 0, failed_attempts = 0, locked_at = NULL, lock_reason = NULL, auto_unlock_at = NULL 
                        WHERE id = ?
                    ")->execute([$_SESSION['admin_id']]);
                    $admin_status_row['account_locked'] = 0;
                }

                if ($admin_status_row['account_locked'] == 1) {
                    $session_email = $admin_status_row['email'] ?? '';
                    destroy_session();
                    throw new ApiException('Your account has been locked. Please use the unlock process.', 401, [
                        'locked' => true,
                        'redirect' => '/views/auth/admin-login.html?locked=1&email=' . urlencode($session_email)
                    ]);
                }
            }

            if ($admin_status_row['status'] === 'suspended') {
                destroy_session();
                throw new ApiException('Your account has been suspended. Contact the system administrator.', 403, [
                    'redirect' => '/views/auth/admin-login.html?suspended=1'
                ]);
            }
        }

        refresh_session_activity();
    }
}
