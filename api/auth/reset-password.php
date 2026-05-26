<?php
/**
 * Finzo Banking System — Reset Password
 * POST endpoint: validates reset token and updates password.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Validate CSRF token
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.'], 403);
}

// Collect inputs
$token            = sanitize_input($_POST['token'] ?? '');
$new_password     = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

// =====================================================
// VALIDATION
// =====================================================

if (empty($token)) {
    json_response(['success' => false, 'message' => 'Reset token is required'], 422);
}

if (empty($new_password)) {
    $errors['new_password'] = 'New password is required';
} else {
    $strength = validate_password_strength($new_password);
    if (!$strength['valid']) {
        $errors['new_password'] = $strength['message'];
    }
}

if ($new_password !== $confirm_password) {
    $errors['confirm_password'] = 'Passwords do not match';
}

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors], 422);
}

// =====================================================
// VALIDATE TOKEN
// =====================================================

$stmt = $pdo->prepare("
    SELECT pr.id, pr.user_id, pr.expires_at, u.full_name, u.email
    FROM password_resets pr
    JOIN users u ON pr.user_id = u.customer_id
    WHERE pr.token = ?
");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    json_response([
        'success' => false,
        'message' => 'Invalid or expired reset link. Please request a new one.'
    ], 400);
}

// Check if token is expired
if (strtotime($reset['expires_at']) < time()) {
    // Delete expired token
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE id = ?");
    $stmt->execute([$reset['id']]);

    json_response([
        'success' => false,
        'message' => 'This reset link has expired. Please request a new one.'
    ], 400);
}

// =====================================================
// UPDATE PASSWORD
// =====================================================

try {
    $pdo->beginTransaction();

    // Hash new password
    $password_hash = password_hash($new_password, PASSWORD_BCRYPT);

    // Update user's password
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE customer_id = ?");
    $stmt->execute([$password_hash, $reset['user_id']]);

    // Delete the used token (and any other tokens for this user)
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $stmt->execute([$reset['user_id']]);

    // Reset failed attempts and unlock if locked
    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE customer_id = ?");
    $stmt->execute([$reset['user_id']]);

    $pdo->commit();

    json_response([
        'success'  => true,
        'message'  => 'Password has been reset successfully. You can now log in with your new password.',
        'redirect' => '/views/auth/login.html'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Reset password error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'An error occurred. Please try again.'], 500);
}
