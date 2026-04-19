<?php
/**
 * Finzo Banking System — Admin Login
 * POST endpoint: authenticates an admin and starts a session.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Note: CSRF protection not required on login endpoints.
// Login cannot be CSRF-exploited as the attacker would need the victim's credentials.

// Collect and sanitize inputs
$email    = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if (empty($email) || empty($password)) {
    json_response(['success' => false, 'message' => 'Email and password are required'], 422);
}

// =====================================================
// FIND ADMIN
// =====================================================

$stmt = $pdo->prepare("
    SELECT id, full_name, email, password_hash, status
    FROM admin_users
    WHERE email = ?
");
$stmt->execute([$email]);
$admin = $stmt->fetch();

if (!$admin) {
    json_response(['success' => false, 'message' => 'Invalid email or password'], 401);
}

// =====================================================
// VERIFY PASSWORD
// =====================================================

if (!password_verify($password, $admin['password_hash'])) {
    json_response(['success' => false, 'message' => 'Invalid email or password'], 401);
}

// =====================================================
// CHECK ACCOUNT STATUS
// =====================================================

if ($admin['status'] !== 'active') {
    json_response([
        'success' => false,
        'message' => 'Your admin account is inactive.'
    ], 403);
}

// =====================================================
// SUCCESSFUL LOGIN
// =====================================================

$ip = get_client_ip();

// Update last login info
$stmt = $pdo->prepare("UPDATE admin_users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?");
$stmt->execute([$ip, $admin['id']]);

// Set session variables
$_SESSION['admin_id']      = (int)$admin['id'];
$_SESSION['admin_name']    = $admin['full_name'];
$_SESSION['admin_email']   = $admin['email'];
$_SESSION['role']          = 'admin';
$_SESSION['last_activity'] = time();

// Write audit log
write_audit_log($pdo, 'admin_login', $admin['id'], 'admin', $admin['id'], 'admin',
    "Admin login from IP: {$ip}");

// Build redirect relative to project root (works in /Finzo/ subdirectory)
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
$redirect = $base . '/views/admin/admin-dashboard.html';

json_response([
    'success'  => true,
    'message'  => 'Login successful',
    'redirect' => $redirect
]);
