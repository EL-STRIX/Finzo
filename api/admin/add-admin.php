<?php
/**
 * Finzo Banking System — Add Admin API
 * POST endpoint: allows super_admin to create new admins.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid or expired session token'], 403);
}

if ($_SESSION['admin_role'] !== 'super_admin') {
    json_response(['success' => false, 'message' => 'Forbidden: Only Super Administrator can perform this action.'], 403);
}

try {
    $full_name = clean_input($_POST['full_name'] ?? '');
    $date_of_birth = clean_input($_POST['date_of_birth'] ?? '');
    $gender = clean_input($_POST['gender'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $admin_role = clean_input($_POST['admin_role'] ?? '');
    $security_question = clean_input($_POST['security_question'] ?? '');
    $security_answer = $_POST['security_answer'] ?? '';

    // Validations
    if (strlen($full_name) < 2 || strlen($full_name) > 100) {
        json_response(['success' => false, 'field' => 'full_name', 'message' => 'Full name must be between 2 and 100 characters'], 422);
    }
    if (empty($date_of_birth) || strtotime($date_of_birth) > strtotime('-18 years')) {
        json_response(['success' => false, 'field' => 'date_of_birth', 'message' => 'Must be at least 18 years old'], 422);
    }
    if (!in_array($gender, ['Male', 'Female', 'Other', 'Prefer not to say'])) {
        json_response(['success' => false, 'field' => 'gender', 'message' => 'Invalid gender selection'], 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['success' => false, 'field' => 'email', 'message' => 'Invalid email format'], 422);
    }
    if (!validate_phone($phone)) {
        json_response(['success' => false, 'field' => 'phone', 'message' => 'Invalid phone number (must be 10 digits)'], 422);
    }
    
    // Check Email unique in admin
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_response(['success' => false, 'field' => 'email', 'message' => 'An admin with this email already exists'], 409);
    }

    if ($password !== $confirm_password) {
        json_response(['success' => false, 'field' => 'confirm_password', 'message' => 'Passwords do not match'], 422);
    }
    
    // Pwd strength: min 8, 1 upper, 1 number, 1 special
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
        json_response(['success' => false, 'field' => 'password', 'message' => 'Password must be min 8 chars with uppercase, number, and special character'], 422);
    }

    if (!in_array($admin_role, ['admin', 'moderator'])) {
        json_response(['success' => false, 'field' => 'admin_role', 'message' => 'Invalid role assigned'], 422);
    }

    if (empty($security_question) || empty($security_answer)) {
        json_response(['success' => false, 'message' => 'Security question and answer are required'], 422);
    }

    // Handle Upload
    $photo_path = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['jpg', 'jpeg', 'png']) || !in_array($file['type'], ['image/jpeg', 'image/png'])) {
            json_response(['success' => false, 'message' => 'Profile photo must be JPG or PNG'], 422);
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            json_response(['success' => false, 'message' => 'Profile photo max size is 5MB'], 422);
        }

        $upload_dir = dirname(__DIR__, 2) . '/uploads/admin-photos/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $filename = 'admin_' . uniqid() . '.' . $ext;
        $dest = $upload_dir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            json_response(['success' => false, 'message' => 'Failed to save profile photo'], 500);
        }
        $photo_path = '/uploads/admin-photos/' . $filename;
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $security_answer_lower = strtolower(trim($security_answer));
    $answer_hash = password_hash($security_answer_lower, PASSWORD_BCRYPT);
    $access_level = ($admin_role === 'admin') ? 'full' : 'restricted';


    // Generate next Admin ID (ADM00x)
    $id_stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(admin_id, 4) AS UNSIGNED)) as max_id FROM admin_users");
    $max_id = $id_stmt->fetchColumn() ?: 0;
    $next_admin_id = 'ADM' . str_pad($max_id + 1, 3, '0', STR_PAD_LEFT);

    // Fetch creator's alphanumeric admin_id (e.g., STF001)
    $creator_stmt = $pdo->prepare("SELECT admin_id FROM admin_users WHERE id = ?");
    $creator_stmt->execute([$_SESSION['admin_id']]);
    $creator_admin_id = $creator_stmt->fetchColumn() ?: null;

    $stmt = $pdo->prepare("
        INSERT INTO admin_users (
            admin_id, full_name, date_of_birth, gender, profile_photo, email, phone, 
            password_hash, security_question, security_answer_hash, security_question_set,
            admin_role, access_level, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, 'active', ?)
    ");
    
    $stmt->execute([
        $next_admin_id, $full_name, $date_of_birth, $gender, $photo_path, $email, $phone,
        $password_hash, $security_question, $answer_hash,
        $admin_role, $access_level, $creator_admin_id
    ]);
    
    $new_id = $pdo->lastInsertId();

    write_audit_log($pdo, 'admin_created', $next_admin_id, 'admin', $_SESSION['admin_id'], 'admin', 
        "New admin created: {$full_name} | Role: {$admin_role}");

    json_response([
        'success' => true, 
        'message' => 'Admin added successfully', 
        'admin_id' => $next_admin_id, 
        'admin_name' => $full_name
    ]);

} catch (PDOException $e) {
    error_log("Add Admin Error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'A system error occurred. Please try again.'], 500);
}
