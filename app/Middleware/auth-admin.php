<?php
/**
 * Finzo Banking System — Admin Auth Middleware
 * Include this file at the top of every admin-facing API endpoint.
 * Validates that the user has an active admin session.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/functions.php';

// Check if session has required admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ||
        isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    ) {
        json_response([
            'success' => false,
            'message' => 'Authentication required. Please log in.',
            'redirect' => '/views/auth/admin-login.html'
        ], 401);
    }

    header('Location: ' . APP_URL . '/views/auth/admin-login.html');
    exit;
}

// Check if admin_id exists in session
if (!isset($_SESSION['admin_id'])) {
    destroy_session();
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ||
        isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    ) {
        json_response([
            'success' => false,
            'message' => 'Session expired. Please log in again.',
            'redirect' => '/views/auth/admin-login.html'
        ], 401);
    }
    header('Location: ' . APP_URL . '/views/auth/admin-login.html');
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
            'redirect' => '/views/auth/admin-login.html'
        ], 401);
    }
    header('Location: ' . APP_URL . '/views/auth/admin-login.html');
    exit;
}

// Fetch and cache admin_role if not in session
if (!isset($_SESSION['admin_role'])) {
    $role_stmt = $pdo->prepare("SELECT admin_role FROM admin_users WHERE id = ?");
    $role_stmt->execute([$_SESSION['admin_id']]);
    $role = $role_stmt->fetchColumn();
    // Cache it to avoid repeated DB hits
    $_SESSION['admin_role'] = $role ?: 'admin'; // fallback just in case
}

// =====================================================
// REAL-TIME LOCK & SUSPENSION CHECK
// Runs on every request to catch locks that happen
// while an admin session is still active.
// =====================================================
$lock_stmt = $pdo->prepare("SELECT account_locked, auto_unlock_at, status, email FROM admin_users WHERE id = ?");
$lock_stmt->execute([$_SESSION['admin_id']]);
$admin_status_row = $lock_stmt->fetch(PDO::FETCH_ASSOC);

if ($admin_status_row) {
    if ($admin_status_row['account_locked'] == 1) {
        // Check if auto-unlock time has passed
        if ($admin_status_row['auto_unlock_at'] && strtotime($admin_status_row['auto_unlock_at']) <= time()) {
            // Auto-unlock the account silently
            $pdo->prepare("
                UPDATE admin_users 
                SET account_locked = 0, failed_attempts = 0, locked_at = NULL, lock_reason = NULL, auto_unlock_at = NULL 
                WHERE id = ?
            ")->execute([$_SESSION['admin_id']]);
            
            // Re-fetch to continue
            $admin_status_row['account_locked'] = 0;
        }

        if ($admin_status_row['account_locked'] == 1) {
            $session_email = $admin_status_row['email'] ?? '';
            destroy_session();
            if (
                isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ||
                isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
            ) {
                json_response([
                    'success' => false,
                    'locked'  => true,
                    'message' => 'Your account has been locked. Please use the unlock process.',
                    'redirect' => '/views/auth/admin-login.html?locked=1&email=' . urlencode($session_email)
                ], 401);
            }
            header('Location: ' . APP_URL . '/views/auth/admin-login.html?locked=1&email=' . urlencode($session_email));
            exit;
        }
    }

    if ($admin_status_row['status'] === 'suspended') {
        destroy_session();
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ||
            isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        ) {
            json_response([
                'success' => false,
                'message' => 'Your account has been suspended. Contact the system administrator.',
                'redirect' => '/views/auth/admin-login.html?suspended=1'
            ], 403);
        }
        header('Location: ' . APP_URL . '/views/auth/admin-login.html?suspended=1');
        exit;
    }
}

// Refresh session activity timestamp
refresh_session_activity();

