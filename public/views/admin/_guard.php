<?php
/**
 * Finzo Admin Portal Guard
 * Server-side protection: blocks anyone without an active ADMIN session
 * from rendering admin view shells. Data endpoints are separately
 * protected by AdminAuthMiddleware on the API layer.
 */
if (!defined('FINZO_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}
\App\Shared\Security\SessionManager::start();

$guardUser = \App\Shared\Security\Auth::user();
if (empty($guardUser) || ($guardUser['type'] ?? '') !== 'admin') {
    header('Location: /Finzo/public/views/auth/admin-login.php');
    exit;
}
