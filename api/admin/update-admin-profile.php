<?php
/**
 * Finzo Banking System — Update Admin Profile API
 * POST endpoint: allows any admin to update their personal details and password.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Special case for 2FA JSON POST handling
$is_json = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
if ($is_json) {
    $json_input = json_decode(file_get_contents('php://input'), true);
    if ($json_input !== null) {
        $_POST = array_merge($_POST, $json_input);
    }
}

// Note: If updating just 2fa from toggle, we shouldn't fail if other fields are missing.
if (isset($_POST['two_fa_enabled']) && count($_POST) <= 2) {
    // Just updating 2FA
    $enable = intval($_POST['two_fa_enabled']);
    try {
        $stmt = $pdo->prepare("UPDATE admin_users SET two_fa_enabled = ? WHERE id = ?");
        $stmt->execute([$enable, $_SESSION['admin_id']]);
        json_response(['success' => true, 'message' => '2FA settings updated']);
    } catch (PDOException $e) {
        json_response(['success' => false, 'message' => 'Failed to update 2FA'], 500);
    }
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid or expired session token'], 403);
}

$admin_id = $_SESSION['admin_id'];

try {
    // 1. Password Update Logic
    if (!empty($_POST['new_password'])) {
        $current_pwd = $_POST['current_password'] ?? '';
        $new_pwd = $_POST['new_password'];
        $confirm_pwd = $_POST['confirm_new_password'] ?? '';

        if ($new_pwd !== $confirm_pwd) {
            json_response(['success' => false, 'field' => 'confirm_new_password', 'message' => 'Passwords do not match'], 422);
        }

        $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $current_hash = $stmt->fetchColumn();

        if (!password_verify($current_pwd, $current_hash)) {
            json_response(['success' => false, 'field' => 'current_password', 'message' => 'Incorrect current password'], 422);
        }
        if ($new_pwd === $current_pwd) {
            json_response(['success' => false, 'field' => 'new_password', 'message' => 'New password cannot be the same as current'], 422);
        }
        if (strlen($new_pwd) < 8 || !preg_match('/[A-Z]/', $new_pwd) || !preg_match('/[0-9]/', $new_pwd) || !preg_match('/[^a-zA-Z0-9]/', $new_pwd)) {
            json_response(['success' => false, 'field' => 'new_password', 'message' => 'Password must be min 8 chars with uppercase, number, and special character'], 422);
        }

        $new_hash = password_hash($new_pwd, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ?, last_password_changed = NOW() WHERE id = ?");
        $stmt->execute([$new_hash, $admin_id]);
        
        write_audit_log($pdo, 'admin_password_updated', $admin_id, 'admin', $admin_id, 'admin', 'Password updated successfully');
        json_response(['success' => true, 'message' => 'Password updated successfully']);
    }

    // 2. General Profile Update Logic
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $date_of_birth = sanitize_input($_POST['date_of_birth'] ?? '');
    $gender = sanitize_input($_POST['gender'] ?? '');
    $security_question = sanitize_input($_POST['security_question'] ?? '');
    $security_answer = $_POST['security_answer'] ?? '';

    // Validations (Email is read-only)
    if (strlen($full_name) < 2 || strlen($full_name) > 100) {
        json_response(['success' => false, 'field' => 'full_name', 'message' => 'Full name must be between 2 and 100 characters'], 422);
    }
    if (!empty($date_of_birth) && strtotime($date_of_birth) > strtotime('-18 years')) {
        json_response(['success' => false, 'field' => 'date_of_birth', 'message' => 'Must be at least 18 years old'], 422);
    }
    if (!empty($phone) && !validate_phone($phone)) {
        json_response(['success' => false, 'field' => 'phone', 'message' => 'Invalid phone number format'], 422);
    }

    $updates = [
        'full_name' => $full_name,
        'phone' => $phone,
        'date_of_birth' => $date_of_birth,
        'gender' => $gender,
        'security_question' => $security_question
    ];
    $params = array_values($updates);
    
    $query = "UPDATE admin_users SET full_name=?, phone=?, date_of_birth=?, gender=?, security_question=?";

    if (!empty($security_answer)) {
        $query .= ", security_answer_hash=?";
        $params[] = password_hash($security_answer, PASSWORD_BCRYPT);
    }
    
    $query .= " WHERE id=?";
    $params[] = $admin_id;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    write_audit_log($pdo, 'admin_profile_updated', $admin_id, 'admin', $admin_id, 'admin', 'Profile information updated');

    json_response(['success' => true, 'message' => 'Profile updated successfully']);

} catch (PDOException $e) {
    error_log("Update Admin Profile Error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to update profile. Please try again.'], 500);
}
