<?php
/**
 * Finzo Banking System — Logout
 * GET/POST endpoint: destroys the session and redirects to login page.
 * Works for all roles (customer, staff, admin).
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once dirname(__DIR__, 3) . '/config/functions.php';

// Determine the role before destroying session for proper redirect
$role = $_SESSION['role'] ?? '';

// Destroy the session
destroy_session();

// Redirect based on role
switch ($role) {
    case 'staff':
        $redirect = '/views/auth/staff-login.html';
        break;
    case 'admin':
        $redirect = '/views/auth/admin-login.html';
        break;
    case 'customer':
    default:
        $redirect = '/views/auth/login.html';
        break;
}

// Handle AJAX requests
if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ||
    isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
) {
    json_response([
        'success'  => true,
        'message'  => 'Logged out successfully',
        'redirect' => $redirect
    ]);
}

// Browser redirect
header('Location: ' . APP_URL . $redirect);
exit;
