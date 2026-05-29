<?php
/**
 * Finzo Banking System — Customer Registration
 * POST endpoint: creates a new customer account.
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

// Collect and sanitize inputs
$full_name        = clean_input($_POST['full_name'] ?? '');
$email            = trim($_POST['email'] ?? '');
$phone            = clean_input($_POST['phone'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

// =====================================================
// VALIDATION
// =====================================================

// Full name: required, 2-100 characters
if (empty($full_name)) {
    $errors['full_name'] = 'Full name is required';
} elseif (strlen($full_name) < 2 || strlen($full_name) > 100) {
    $errors['full_name'] = 'Full name must be between 2 and 100 characters';
}

// Email: valid format
if (empty($email)) {
    $errors['email'] = 'Email address is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address';
}

// Phone: 10-digit Indian mobile
if (empty($phone)) {
    $errors['phone'] = 'Phone number is required';
} elseif (!validate_phone($phone)) {
    $errors['phone'] = 'Please enter a valid 10-digit Indian mobile number';
}

// Password: minimum 8 characters
if (empty($password)) {
    $errors['password'] = 'Password is required';
} else {
    $strength = validate_password_strength($password);
    if (!$strength['valid']) {
        $errors['password'] = $strength['message'];
    }
}

// Confirm password: must match
if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Passwords do not match';
}

// Return validation errors if any
if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors], 422);
}

// =====================================================
// UNIQUENESS CHECKS
// =====================================================

// Check email uniqueness
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    json_response(['success' => false, 'errors' => ['email' => 'This email is already registered']], 409);
}

// Check phone uniqueness
$stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
$stmt->execute([$phone]);
if ($stmt->fetch()) {
    json_response(['success' => false, 'errors' => ['phone' => 'This phone number is already registered']], 409);
}

// =====================================================
// CREATE USER
// =====================================================

try {
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, phone, password_hash, status, created_at)
        VALUES (?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$full_name, $email, $phone, $password_hash]);

    $user_id = $pdo->lastInsertId();

    $seq_stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(customer_id, 9) AS UNSIGNED)) as max_id FROM users WHERE customer_id LIKE 'FNZ-USR-%'");
    $max_seq = $seq_stmt->fetchColumn() ?: 0;
    $customer_id = 'FNZ-USR-' . str_pad($max_seq + 1, 3, '0', STR_PAD_LEFT);
    $updateStmt = $pdo->prepare("UPDATE users SET customer_id = ? WHERE id = ?");
    $updateStmt->execute([$customer_id, $user_id]);

    // Start session for the new user
    $_SESSION['user_id']     = (int)$user_id;
    $_SESSION['user_name']   = $full_name;
    $_SESSION['user_email']  = $email;
    $_SESSION['user_phone']  = $phone;
    $_SESSION['user_status'] = 'pending';
    $_SESSION['role']        = 'customer';
    $_SESSION['last_activity'] = time();

    // =====================================================
    // INSERT DEFAULT NOTIFICATIONS
    // =====================================================
    $stmt = $pdo->prepare("
    INSERT INTO notifications (user_id, type, title, message)
    VALUES (?, 'system', 'Welcome to Finzo!', ?)
    ");
    $welcomeMsg = "Your account has been registered. Complete your profile to start banking.";
    $stmt->execute([$customer_id, $welcomeMsg]);
    
    $stmt = $pdo->prepare("
    INSERT INTO notifications (user_id, type, title, message)
    VALUES (?, 'security', 'Profile Incomplete', ?)
    ");
    $profileMsg = "Please complete your identity verification to unlock full banking services.";
    $stmt->execute([$customer_id, $profileMsg]);

    // Build redirect relative to project root (consistent with login.php)
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
    $redirect = $base . '/views/user/dashboard.html';

    json_response([
        'success'  => true,
        'message'  => 'Registration successful! Please complete your profile.',
        'redirect' => $redirect
    ], 201);

} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Registration failed. Please try again.'], 500);
}
