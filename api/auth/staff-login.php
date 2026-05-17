<?php
/**
 * api/auth/staff-login.php
 * POST endpoint: authenticates a staff member and starts a session.
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$identity = sanitize_input($_POST['identity'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($identity) || empty($password)) {
    json_response(['success' => false, 'message' => 'Staff ID/Email and password are required'], 422);
}

$stmt = $pdo->prepare("
    SELECT id, staff_id, full_name, personal_email, password_hash, status, branch_id, designation,
           failed_attempts, locked_until
    FROM staff
    WHERE staff_id = ? OR personal_email = ?
");
$stmt->execute([$identity, $identity]);
$staff = $stmt->fetch();

if (!$staff) {
    json_response(['success' => false, 'message' => 'Invalid Staff ID or password'], 401);
}

// Check if account is locked due to too many failed attempts
if ($staff['failed_attempts'] >= 5 && $staff['locked_until'] && strtotime($staff['locked_until']) > time()) {
    json_response(['success' => false, 'message' => 'Account locked due to too many failed attempts. Try again later.'], 403);
}

if (!password_verify($password, $staff['password_hash'])) {
    $pdo->prepare("UPDATE staff SET failed_attempts = failed_attempts + 1, locked_until = IF(failed_attempts + 1 >= 5, DATE_ADD(NOW(), INTERVAL 30 MINUTE), locked_until) WHERE id = ?")->execute([$staff['id']]);
    json_response(['success' => false, 'message' => 'Invalid Staff ID or password'], 401);
}

if ($staff['status'] === 'suspended') {
    json_response([
        'success' => false,
        'message' => 'Your account has been suspended. Please contact the administrator.'
    ], 403);
}

$ip = get_client_ip();

$pdo->prepare("UPDATE staff SET failed_attempts = 0, locked_until = NULL, last_login_at = NOW(), last_login_ip = ? WHERE id = ?")->execute([$ip, $staff['id']]);

$_SESSION['staff_id']         = (int)$staff['id'];
$_SESSION['staff_display_id'] = $staff['staff_id'];
$_SESSION['staff_name']       = $staff['full_name'];
$_SESSION['staff_email']      = $staff['personal_email'];
$_SESSION['staff_branch_id']  = $staff['branch_id'];
$_SESSION['staff_role']       = $staff['designation'];
$_SESSION['role']             = 'staff';
$_SESSION['last_activity']    = time();

write_audit_log($pdo, 'staff_login', $staff['id'], 'staff', $staff['id'], 'staff',
    "Staff login from IP: {$ip}");

$base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
$redirect = $base . '/views/staff/staff-dashboard.html';

json_response([
    'success'  => true,
    'message'  => 'Login successful',
    'redirect' => $redirect
]);
