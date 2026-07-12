<?php
/**
 * Finzo Banking System — Staff Auth Middleware
 * Include this file at the top of every staff-facing API endpoint.
 * Validates that the user has an active staff session.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

// Check if session has required staff role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ||
        isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    ) {
        json_response([
            'success' => false,
            'message' => 'Authentication required. Please log in.',
            'redirect' => '/views/auth/staff-login.html'
        ], 401);
    }

    header('Location: ' . APP_URL . '/views/auth/staff-login.html');
    exit;
}

// Check if staff_id exists in session
if (!isset($_SESSION['staff_id'])) {
    destroy_session();
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ||
        isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    ) {
        json_response([
            'success' => false,
            'message' => 'Session expired. Please log in again.',
            'redirect' => '/views/auth/staff-login.html'
        ], 401);
    }
    header('Location: ' . APP_URL . '/views/auth/staff-login.html');
    exit;
}

// Check session expiry
if (is_session_expired()) {
    destroy_session();
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ||
        isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    ) {
        json_response([
            'success' => false,
            'message' => 'Session expired. Please log in again.',
            'redirect' => '/views/auth/staff-login.html'
        ], 401);
    }
    header('Location: ' . APP_URL . '/views/auth/staff-login.html');
    exit;
}

// Refresh session activity timestamp
refresh_session_activity();
