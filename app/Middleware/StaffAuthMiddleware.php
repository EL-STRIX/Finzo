<?php
namespace App\Middleware;

use App\Core\Exceptions\ApiException;

class StaffAuthMiddleware {
    public function handle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
            throw new ApiException('Authentication required. Please log in.', 401, ['redirect' => '/views/auth/staff-login.html']);
        }

        if (!isset($_SESSION['staff_id'])) {
            destroy_session();
            throw new ApiException('Session expired. Please log in again.', 401, ['redirect' => '/views/auth/staff-login.html']);
        }

        if (is_session_expired()) {
            destroy_session();
            throw new ApiException('Session expired. Please log in again.', 401, ['redirect' => '/views/auth/staff-login.html']);
        }

        refresh_session_activity();
    }
}
