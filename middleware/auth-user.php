<?php
/**
 * Finzo Banking System — Customer Auth Middleware
 * Include this file at the top of every customer-facing API endpoint.
 * Validates that the user has an active customer session.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/functions.php';

// Check if session has required customer role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    // If this is an API/AJAX request, return JSON
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ||
        isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    ) {
        json_response([
            'success' => false,
            'message' => 'Authentication required. Please log in.',
            'redirect' => '/views/auth/login.html'
        ], 401);
    }

    // Otherwise redirect to login page
    header('Location: ' . APP_URL . '/views/auth/login.html');
    exit;
}

// Check if user_id exists in session
if (!isset($_SESSION['user_id'])) {
    destroy_session();
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ||
        isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    ) {
        json_response([
            'success' => false,
            'message' => 'Session expired. Please log in again.',
            'redirect' => '/views/auth/login.html'
        ], 401);
    }
    header('Location: ' . APP_URL . '/views/auth/login.html');
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
            'redirect' => '/views/auth/login.html'
        ], 401);
    }
    header('Location: ' . APP_URL . '/views/auth/login.html');
    exit;
}

// Refresh session activity timestamp
refresh_session_activity();
