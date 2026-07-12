<?php
/**
 * Finzo Banking System — CSRF Token Provider
 * Returns a valid CSRF token for static HTML forms.
 */

require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__, 3) . '/config/functions.php';

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Generate token (this handles session_start and session storage)
$token = generate_csrf_token();

json_response([
    'success' => true,
    'csrf_token' => $token
]);
