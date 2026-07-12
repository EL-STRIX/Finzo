<?php
/**
 * Finzo Banking System — Change Password API
 * POST endpoint: authenticated password change with current password verification.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-user.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Validate CSRF token
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid security token'], 403);
}

$user_id = $_SESSION['user_id'];

// Collect inputs
$current_password = $_POST['current_password'] ?? '';
$new_password     = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

// =====================================================
// VALIDATION
// =====================================================

if (empty($current_password)) {
    $errors['current_password'] = 'Current password is required';
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

if ($current_password === $new_password && !empty($new_password)) {
    $errors['new_password'] = 'New password must be different from current password';
}

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors], 422);
}

// =====================================================
// VERIFY CURRENT PASSWORD
// =====================================================

try {
    $stmt = $pdo->prepare("SELECT customer_id, password_hash FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current_password, $user['password_hash'])) {
        json_response(['success' => false, 'errors' => ['current_password' => 'Current password is incorrect']], 401);
    }

    // =====================================================
    // UPDATE PASSWORD
    // =====================================================

    $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$new_hash, $user_id]);

    // Send security notification
    send_notification($pdo, $user['customer_id'], 'security',
        'Password Changed',
        'Your account password was changed successfully. If you did not make this change, please contact support immediately.'
    );

    json_response([
        'success' => true,
        'message' => 'Password changed successfully'
    ]);

} catch (PDOException $e) {
    error_log("Change password error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to change password'], 500);
}
