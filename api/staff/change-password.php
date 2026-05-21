<?php
/**
 * Finzo Banking System — Staff Change Password API
 * POST endpoint: allows staff to change their own password.
 * Version 1.0 — May 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-staff.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Validate CSRF token
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid security token'], 403);
}

$staff_id = $_SESSION['staff_id'];

// =====================================================
// INPUT VALIDATION
// =====================================================
$current_password  = $_POST['current_password'] ?? '';
$new_password      = $_POST['new_password'] ?? '';
$confirm_password  = $_POST['confirm_password'] ?? '';

$errors = [];

if (empty($current_password)) {
    $errors['current_password'] = 'Current password is required';
}

if (empty($new_password)) {
    $errors['new_password'] = 'New password is required';
}

if (empty($confirm_password)) {
    $errors['confirm_password'] = 'Please confirm your new password';
}

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors], 422);
}

// Validate new password strength
$strength = validate_password_strength($new_password);
if (!$strength['valid']) {
    json_response(['success' => false, 'errors' => ['new_password' => $strength['message']]], 422);
}

// Confirm passwords match
if ($new_password !== $confirm_password) {
    json_response(['success' => false, 'errors' => ['confirm_password' => 'Passwords do not match']], 422);
}

// =====================================================
// VERIFY CURRENT PASSWORD & UPDATE
// =====================================================
try {
    // Fetch current password hash
    $stmt = $pdo->prepare("SELECT password_hash FROM staff WHERE id = ?");
    $stmt->execute([$staff_id]);
    $row = $stmt->fetch();

    if (!$row) {
        json_response(['success' => false, 'message' => 'Staff account not found'], 404);
    }

    // Verify current password
    if (!password_verify($current_password, $row['password_hash'])) {
        json_response(['success' => false, 'errors' => ['current_password' => 'Current password is incorrect']], 401);
    }

    // Hash new password and update
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE staff SET password_hash = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$new_hash, $staff_id]);

    // Audit log
    write_audit_log($pdo, 'staff_password_change', $staff_id, 'staff', $staff_id, 'staff',
        'Staff changed their password');

    json_response([
        'success' => true,
        'message' => 'Password changed successfully'
    ]);

} catch (PDOException $e) {
    error_log("Staff change password error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to change password. Please try again.'], 500);
}
