<?php
/**
 * Finzo Banking System — Admin Add Staff API
 * POST endpoint: creates a new staff account.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$admin_id = $_SESSION['admin_id'];

// Collect inputs
$full_name = sanitize_input($_POST['full_name'] ?? '');
$email     = sanitize_input($_POST['email'] ?? '');
$phone     = sanitize_input($_POST['phone'] ?? '');
$branch_id = intval($_POST['branch_id'] ?? 0);
$role      = sanitize_input($_POST['role'] ?? 'teller');
$password  = $_POST['password'] ?? '';

$errors = [];

// Validation
if (empty($full_name) || strlen($full_name) < 2) {
    $errors['full_name'] = 'Full name is required (min 2 characters)';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Valid email is required';
}

if (empty($phone) || !validate_phone($phone)) {
    $errors['phone'] = 'Valid 10-digit phone number is required';
}

if (!in_array($role, ['teller', 'manager', 'supervisor'])) {
    $errors['role'] = 'Invalid role selection';
}

if (empty($password) || strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
}

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors], 422);
}

try {
    // Check email uniqueness
    $stmt = $pdo->prepare("SELECT id FROM staff WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_response(['success' => false, 'errors' => ['email' => 'This email is already in use']], 409);
    }

    // Generate staff ID
    $staff_id_str = generate_staff_id($pdo);

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Validate branch exists (if provided)
    if ($branch_id > 0) {
        $stmt = $pdo->prepare("SELECT id FROM branches WHERE id = ?");
        $stmt->execute([$branch_id]);
        if (!$stmt->fetch()) {
            json_response(['success' => false, 'errors' => ['branch_id' => 'Invalid branch selected']], 422);
        }
    } else {
        $branch_id = null;
    }

    // Insert staff
    $stmt = $pdo->prepare("
        INSERT INTO staff (staff_id, full_name, email, phone, password_hash, branch_id, role, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    $stmt->execute([$staff_id_str, $full_name, $email, $phone, $password_hash, $branch_id, $role]);

    // Audit log
    write_audit_log($pdo, 'staff_created', $staff_id_str, 'staff', $admin_id, 'admin',
        "Created staff: {$full_name} ({$staff_id_str}), Role: {$role}");

    json_response([
        'success'  => true,
        'message'  => 'Staff member created successfully',
        'staff_id' => $staff_id_str
    ], 201);

} catch (PDOException $e) {
    error_log("Add staff error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to create staff account'], 500);
}
