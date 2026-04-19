<?php
/**
 * Finzo Banking System — CSRF Token Provider
 * Returns a valid CSRF token for static HTML forms.
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

// Generate token (this handles session_start and session storage)
$token = generate_csrf_token();

json_response([
    'success' => true,
    'csrf_token' => $token
]);
