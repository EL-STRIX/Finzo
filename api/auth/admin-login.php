<?php
/**
 * Finzo Banking System — Admin Login
 * POST endpoint: authenticates an admin and handles lockouts.
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// 1. Validate CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (empty($csrf_token)) {
    json_response(['success' => false, 'message' => 'Security token missing. Please refresh the page.'], 403);
}
if (!validate_csrf_token($csrf_token)) {
    json_response(['success' => false, 'message' => 'Invalid or expired security session. Please try again.'], 403);
}

// 2. Sanitize inputs
$email    = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    json_response(['success' => false, 'message' => 'Email and password are required'], 422);
}

// 3. Query admin_users
$stmt = $pdo->prepare("
    SELECT id, full_name, email, profile_photo, password_hash, status, admin_role, 
           failed_attempts, account_locked, locked_at, auto_unlock_at, answer_locked_until, security_question_set,
           temp_password_expiry
    FROM admin_users
    WHERE email = ?
");
$stmt->execute([$email]);
$admin = $stmt->fetch();

// 4. If no record found
if (!$admin) {
    json_response(['success' => false, 'message' => 'Invalid email or password'], 401);
}

// 4.1 Check temporary password expiry
if ($admin['temp_password_expiry'] && strtotime($admin['temp_password_expiry']) < time()) {
    json_response([
        'success' => false,
        'message' => 'Your temporary password has expired. Please contact a super administrator for a new one.'
    ], 401);
}

// 5. If status = 'suspended'
if ($admin['status'] === 'suspended') {
    json_response([
        'success' => false,
        'message' => 'Your account has been suspended. Contact the system administrator.'
    ], 403);
}

// 6. If account_locked = 1
if ($admin['account_locked'] == 1) {
    // Check if auto-unlock time has passed
    if ($admin['auto_unlock_at'] && strtotime($admin['auto_unlock_at']) <= time()) {
        // Auto-unlock the account
        $pdo->prepare("
            UPDATE admin_users 
            SET account_locked = 0, failed_attempts = 0, locked_at = NULL, lock_reason = NULL, auto_unlock_at = NULL 
            WHERE id = ?
        ")->execute([$admin['id']]);
        
        // Refresh admin data to continue
        $admin['account_locked'] = 0;
        $admin['failed_attempts'] = 0;
    } else {
        json_response([
            'success' => false,
            'locked' => true,
            'security_question_set' => (bool)$admin['security_question_set'],
            'auto_unlock_at' => $admin['auto_unlock_at'],
            'message' => 'Account locked.'
        ], 401);
    }
}

// 7. If account NOT locked, verify password
$ip = get_client_ip();

if (!password_verify($password, $admin['password_hash'])) {
    $new_attempts = (int)$admin['failed_attempts'] + 1;
    
    if ($new_attempts >= 3) {
        // Lock account
        $updateStmt = $pdo->prepare("
            UPDATE admin_users 
            SET account_locked = 1, 
                locked_at = NOW(), 
                failed_attempts = 3, 
                lock_reason = 'failed_password',
                auto_unlock_at = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
            WHERE id = ?
        ");
        $updateStmt->execute([$admin['id']]);
        
        // Re-fetch to get the auto_unlock_at we just set
        $st = $pdo->prepare("SELECT auto_unlock_at FROM admin_users WHERE id = ?");
        $st->execute([$admin['id']]);
        $auto_unlock_at = $st->fetchColumn();
        
        // Insert into admin_lock_log
        $logStmt = $pdo->prepare("
            INSERT INTO admin_lock_log (admin_id, event_type, ip_address, details) 
            SELECT admin_id, 'locked_password', ?, 'Locked after 3 failed password attempts'
            FROM admin_users WHERE id = ?
        ");
        $logStmt->execute([$ip, $admin['id']]);
        
        // Write audit log
        write_audit_log($pdo, 'admin_account_locked', $admin['id'], 'admin', $admin['id'], 'admin', 'Account locked: 3 wrong passwords');
        
        json_response([
            'success' => false,
            'locked' => true,
            'security_question_set' => (bool)$admin['security_question_set'],
            'auto_unlock_at' => $auto_unlock_at,
            'message' => 'Account locked.'
        ], 401);
    } else {
        // Update failed attempts
        $updateStmt = $pdo->prepare("UPDATE admin_users SET failed_attempts = ? WHERE id = ?");
        $updateStmt->execute([$new_attempts, $admin['id']]);
        
        $remaining = 3 - $new_attempts;
        json_response([
            'success' => false,
            'locked' => false,
            'message' => "Invalid password. {$remaining} attempt(s) remaining before lockout."
        ], 401);
    }
} else {
    // CORRECT password
    $updateStmt = $pdo->prepare("
        UPDATE admin_users 
        SET failed_attempts = 0, account_locked = 0, last_login_at = NOW(), last_login_ip = ? 
        WHERE id = ?
    ");
    $updateStmt->execute([$ip, $admin['id']]);
    
    // Set session variables
    $_SESSION['admin_id']      = (int)$admin['id'];
    $_SESSION['admin_name']    = $admin['full_name'];
    $_SESSION['admin_email']   = $admin['email'];
    $_SESSION['admin_profile_photo']  = $admin['profile_photo'];
    $_SESSION['admin_role']    = $admin['admin_role'];
    $_SESSION['role']          = 'admin';
    $_SESSION['last_activity'] = time();
    
    // Write audit log
    write_audit_log($pdo, 'admin_login', $admin['id'], 'admin', $admin['id'], 'admin', "Admin login from IP: {$ip}");
    
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
    $redirect = $base . '/views/admin/admin-dashboard.html';
    
    json_response([
        'success'  => true,
        'message'  => 'Login successful',
        'redirect' => $redirect
    ]);
}
