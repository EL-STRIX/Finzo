<?php
/**
 * Finzo Banking System — Forgot Password
 * POST endpoint: sends a password reset email with a secure token.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';
require_once dirname(__DIR__, 2) . '/config/mailer.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Validate CSRF token
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.'], 403);
}

// Collect and sanitize input
$email = clean_input($_POST['email'] ?? '');

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['success' => false, 'message' => 'Please enter a valid email address'], 422);
}

// =====================================================
// FIND USER
// =====================================================

$stmt = $pdo->prepare("SELECT id, customer_id, full_name, email FROM users WHERE email = ? AND is_deleted = 0");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Always return success to prevent email enumeration attacks
if (!$user) {
    json_response([
        'success' => true,
        'message' => 'If an account with this email exists, a password reset link has been sent.'
    ]);
}

// =====================================================
// GENERATE RESET TOKEN
// =====================================================

try {
    // Delete any existing reset tokens for this user
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $stmt->execute([$user['customer_id']]);

    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now

    // Store token in database
    $stmt = $pdo->prepare("
        INSERT INTO password_resets (user_id, token, expires_at, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$user['customer_id'], $token, $expires_at]);

    // Send reset email
    send_password_reset_email($user['email'], $user['full_name'], $token);

    json_response([
        'success' => true,
        'message' => 'If an account with this email exists, a password reset link has been sent.'
    ]);

} catch (PDOException $e) {
    error_log("Forgot password error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'An error occurred. Please try again.'], 500);
}
