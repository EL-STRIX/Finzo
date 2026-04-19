<?php
/**
 * Finzo Banking System — Staff Login
 * POST endpoint: authenticates a staff member and starts a session.
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
$identity = sanitize_input($_POST['identity'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if (empty($identity) || empty($password)) {
    json_response(['success' => false, 'message' => 'Staff ID/Email and password are required'], 422);
}

// =====================================================
// FIND STAFF
// =====================================================

$stmt = $pdo->prepare("
    SELECT id, staff_id, full_name, email, password_hash, status, branch_id, role
    FROM staff
    WHERE staff_id = ? OR email = ?
");
$stmt->execute([$identity, $identity]);
$staff = $stmt->fetch();

if (!$staff) {
    json_response(['success' => false, 'message' => 'Invalid Staff ID or password'], 401);
}

// =====================================================
// VERIFY PASSWORD
// =====================================================

if (!password_verify($password, $staff['password_hash'])) {
    json_response(['success' => false, 'message' => 'Invalid Staff ID or password'], 401);
}

// =====================================================
// CHECK ACCOUNT STATUS
// =====================================================

if ($staff['status'] === 'suspended') {
    json_response([
        'success' => false,
        'message' => 'Your account has been suspended. Please contact the administrator.'
    ], 403);
}

// =====================================================
// SUCCESSFUL LOGIN
// =====================================================

$ip = get_client_ip();

// Update last login info
$stmt = $pdo->prepare("UPDATE staff SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?");
$stmt->execute([$ip, $staff['id']]);

// Set session variables
$_SESSION['staff_id']         = (int)$staff['id'];
$_SESSION['staff_display_id'] = $staff['staff_id'];
$_SESSION['staff_name']       = $staff['full_name'];
$_SESSION['staff_email']      = $staff['email'];
$_SESSION['staff_branch_id']  = $staff['branch_id'];
$_SESSION['staff_role']       = $staff['role'];
$_SESSION['role']             = 'staff';
$_SESSION['last_activity']    = time();

// Write audit log
write_audit_log($pdo, 'staff_login', $staff['id'], 'staff', $staff['id'], 'staff',
    "Staff login from IP: {$ip}");

// Build redirect relative to project root (works in /Finzo/ subdirectory)
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
$redirect = $base . '/views/staff/staff-dashboard.html';

json_response([
    'success'  => true,
    'message'  => 'Login successful',
    'redirect' => $redirect
]);
