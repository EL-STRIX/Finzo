<?php
/**
 * Finzo Banking System — Customer Login
 * POST endpoint: authenticates a customer and starts a session.
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
    json_response(['success' => false, 'message' => 'Email/Account Number and password are required'], 422);
}

// =====================================================
// FIND USER
// =====================================================

// Search by Email OR Account Number
$stmt = $pdo->prepare("
    SELECT u.id, u.full_name, u.email, u.phone, u.password_hash, u.status, u.failed_attempts, u.locked_until
    FROM users u
    LEFT JOIN accounts a ON u.id = a.user_id
    WHERE (u.email = ? OR a.account_no = ?) AND u.is_deleted = 0
    LIMIT 1
");
$stmt->execute([$identity, $identity]);
$user = $stmt->fetch();

if (!$user) {
    json_response(['success' => false, 'message' => 'Invalid email or password'], 401);
}

// =====================================================
// CHECK ACCOUNT LOCK
// =====================================================

if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
    $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
    json_response([
        'success' => false,
        'message' => "Account locked due to too many failed attempts. Try again in {$remaining} minute(s)."
    ], 423);
}

// =====================================================
// VERIFY PASSWORD
// =====================================================

if (!password_verify($password, $user['password_hash'])) {
    // Increment failed attempts
    $new_attempts = $user['failed_attempts'] + 1;

    $max_attempts = get_setting($pdo, 'max_failed_login_attempts', 5);
    $lockout_duration = get_setting($pdo, 'customer_lockout_duration', 1800);

    if ($new_attempts >= $max_attempts) {
        // Lock the account
        $lock_until = date('Y-m-d H:i:s', time() + $lockout_duration);
        $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?");
        $stmt->execute([$new_attempts, $lock_until, $user['id']]);

        // Write audit log
        write_audit_log($pdo, 'account_locked', $user['id'], 'user', $user['id'], 'system',
            "Account locked after {$new_attempts} failed login attempts");

        $lockout_minutes = ceil($lockout_duration / 60);
        json_response([
            'success' => false,
            'message' => "Account locked due to too many failed attempts. Try again in {$lockout_minutes} minutes."
        ], 423);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
        $stmt->execute([$new_attempts, $user['id']]);

        $remaining = $max_attempts - $new_attempts;
        json_response([
            'success' => false,
            'message' => "Invalid email or password. {$remaining} attempt(s) remaining."
        ], 401);
    }
}

// =====================================================
// CHECK ACCOUNT STATUS
// =====================================================

if ($user['status'] === 'suspended') {
    json_response([
        'success' => false,
        'message' => 'Your account has been suspended. Please contact support.'
    ], 403);
}

// =====================================================
// SUCCESSFUL LOGIN
// =====================================================

// Reset failed attempts and update login info
$ip = get_client_ip();
$stmt = $pdo->prepare("
    UPDATE users SET failed_attempts = 0, locked_until = NULL, last_login_at = NOW(), last_login_ip = ?
    WHERE id = ?
");
$stmt->execute([$ip, $user['id']]);

// Fetch account info if exists
$acct_stmt = $pdo->prepare("
    SELECT account_no, account_type, balance, status
    FROM accounts
    WHERE user_id = ? AND status IN ('active', 'pending')
    ORDER BY status = 'active' DESC
    LIMIT 1
");
$acct_stmt->execute([$user['id']]);
$account = $acct_stmt->fetch();

// Set session variables
$_SESSION['user_id']        = (int)$user['id'];
$_SESSION['user_name']      = $user['full_name'];
$_SESSION['user_email']     = $user['email'];
$_SESSION['user_phone']     = $user['phone'] ?? '';
$_SESSION['user_status']    = $user['status'];
$_SESSION['user_account_no'] = $account ? $account['account_no'] : null;
$_SESSION['user_balance']   = $account ? $account['balance'] : null;
$_SESSION['role']           = 'customer';
$_SESSION['last_activity']  = time();

// Write audit log
write_audit_log($pdo, 'user_login', $user['id'], 'user', $user['id'], 'system',
    "Customer login from IP: {$ip}");

// Build redirect relative to project root (works in /Finzo/ subdirectory)
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
$redirect = $base . '/views/user/dashboard.html';

json_response([
    'success'  => true,
    'message'  => 'Login successful',
    'redirect' => $redirect,
    'user'     => [
        'full_name'    => $user['full_name'],
        'email'        => $user['email'],
        'status'       => $user['status'],
        'account_no'   => $account ? $account['account_no'] : '',
        'account_type' => $account ? $account['account_type'] : 'savings',
        'balance'      => $account ? $account['balance'] : '0'
    ]
]);
