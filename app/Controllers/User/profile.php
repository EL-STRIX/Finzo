<?php
/**
 * Finzo Banking System — Customer Profile API
 * GET: fetch profile data | POST: update profile
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-user.php';

$user_id = $_SESSION['user_id'];

// =====================================================
// GET: FETCH PROFILE
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT id, customer_id, full_name, email, phone, status, dob, gender, father_name,
                   mother_name, marital_status, nationality, source_of_income, profile_picture,
                   occupation, annual_income, street, city, state, pincode, country,
                   id_type, id_number, created_at
            FROM users
            WHERE id = ? AND is_deleted = 0
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            json_response(['success' => false, 'message' => 'User not found'], 404);
        }

        // Get account info
        $stmt = $pdo->prepare("
            SELECT account_no, account_type, balance, status, opened_date
            FROM accounts WHERE user_id = ? LIMIT 1
        ");
        $stmt->execute([$user['customer_id']]);
        $account = $stmt->fetch();

        json_response([
            'success' => true,
            'user'    => $user,
            'account' => $account
        ]);

    } catch (PDOException $e) {
        error_log("Profile fetch error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to load profile'], 500);
    }
}

// =====================================================
// POST: UPDATE PROFILE
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        json_response(['success' => false, 'message' => 'Invalid security token'], 403);
    }

    $full_name = clean_input($_POST['full_name'] ?? '');
    $phone     = clean_input($_POST['phone'] ?? '');
    $email     = clean_input($_POST['email'] ?? '');

    $errors = [];

    // Validate full_name
    if (empty($full_name) || strlen($full_name) < 2 || strlen($full_name) > 100) {
        $errors['full_name'] = 'Full name must be between 2 and 100 characters';
    }

    // Validate phone
    if (!empty($phone) && !validate_phone($phone)) {
        $errors['phone'] = 'Please enter a valid 10-digit Indian mobile number';
    }

    // Validate email
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    // Check email uniqueness (if changed)
    if (!empty($email) && $email !== $_SESSION['user_email']) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors['email'] = 'This email is already in use by another account';
        }
    }

    // Check phone uniqueness (if changed)
    if (!empty($phone)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
        $stmt->execute([$phone, $user_id]);
        if ($stmt->fetch()) {
            $errors['phone'] = 'This phone number is already in use by another account';
        }
    }

    if (!empty($errors)) {
        json_response(['success' => false, 'errors' => $errors], 422);
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE users SET full_name = ?, phone = ?, email = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$full_name, $phone, $email, $user_id]);

        // Update session
        $_SESSION['user_name']  = $full_name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_phone'] = $phone;

        json_response([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);

    } catch (PDOException $e) {
        error_log("Profile update error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to update profile'], 500);
    }
}

// Method not allowed
json_response(['success' => false, 'message' => 'Method not allowed'], 405);
