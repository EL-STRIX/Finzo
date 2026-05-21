<?php
/**
 * Finzo Banking System — Complete Profile (KYC Submission)
 * POST endpoint: handles the 4-step KYC form submission.
 * Accepts multipart/form-data for file uploads.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-user.php';
require_once dirname(__DIR__, 2) . '/config/mailer.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Validate CSRF token
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid security token'], 403);
}

$user_id = $_SESSION['user_id'];
$errors = [];

// =====================================================
// STEP 1: PERSONAL INFO VALIDATION
// =====================================================
$dob           = sanitize_input($_POST['dob'] ?? '');
$gender        = sanitize_input($_POST['gender'] ?? '');
$father_name   = sanitize_input($_POST['father_name'] ?? '');
$occupation    = sanitize_input($_POST['occupation'] ?? '');
$annual_income = sanitize_input($_POST['annual_income'] ?? '');

if (empty($dob)) {
    $errors['dob'] = 'Date of birth is required';
} else {
    $dob_date = new DateTime($dob);
    $today = new DateTime();
    $age = $dob_date->diff($today)->y;
    if ($age < 18) {
        $errors['dob'] = 'You must be at least 18 years old';
    }
}

if (empty($gender) || !in_array($gender, ['Male', 'Female', 'Other', 'Prefer not to say'])) {
    $errors['gender'] = 'Please select a valid gender';
}

if (empty($father_name) || strlen($father_name) < 2) {
    $errors['father_name'] = "Father's name is required";
}

if (empty($occupation)) {
    $errors['occupation'] = 'Occupation is required';
}

if (empty($annual_income)) {
    $errors['annual_income'] = 'Annual income range is required';
}

// =====================================================
// STEP 2: ADDRESS VALIDATION
// =====================================================
$street  = sanitize_input($_POST['street'] ?? '');
$city    = sanitize_input($_POST['city'] ?? '');
$state   = sanitize_input($_POST['state'] ?? '');
$pincode = sanitize_input($_POST['pincode'] ?? '');

if (empty($street)) {
    $errors['street'] = 'Street address is required';
}

if (empty($city)) {
    $errors['city'] = 'City is required';
}

if (empty($state)) {
    $errors['state'] = 'State is required';
}

if (empty($pincode) || !validate_pincode($pincode)) {
    $errors['pincode'] = 'Please enter a valid 6-digit pincode';
}

// =====================================================
// STEP 3: IDENTITY DOCUMENTS VALIDATION
// =====================================================
$id_type   = sanitize_input($_POST['id_type'] ?? '');
$id_number = sanitize_input($_POST['id_number'] ?? '');

$valid_id_types = ['Aadhaar', 'PAN', 'Passport', 'Voter ID', 'Driving License'];

if (empty($id_type) || !in_array($id_type, $valid_id_types)) {
    $errors['id_type'] = 'Please select a valid document type';
}

if (empty($id_number)) {
    $errors['id_number'] = 'Document number is required';
}

// Validate file uploads
$id_front_path = '';
$id_back_path  = '';

if (!isset($_FILES['id_front']) || $_FILES['id_front']['error'] !== UPLOAD_ERR_OK) {
    $errors['id_front'] = 'Front image of identity document is required';
} else {
    $front = $_FILES['id_front'];
    if ($front['size'] > MAX_UPLOAD_SIZE) {
        $errors['id_front'] = 'Front image must be less than 5MB';
    }
    if (!in_array($front['type'], ALLOWED_IMAGE_TYPES)) {
        $errors['id_front'] = 'Front image must be JPG or PNG format';
    }
}

if (!isset($_FILES['id_back']) || $_FILES['id_back']['error'] !== UPLOAD_ERR_OK) {
    $errors['id_back'] = 'Back image of identity document is required';
} else {
    $back = $_FILES['id_back'];
    if ($back['size'] > MAX_UPLOAD_SIZE) {
        $errors['id_back'] = 'Back image must be less than 5MB';
    }
    if (!in_array($back['type'], ALLOWED_IMAGE_TYPES)) {
        $errors['id_back'] = 'Back image must be JPG or PNG format';
    }
}

// =====================================================
// STEP 4: ACCOUNT PREFERENCES VALIDATION
// =====================================================
$account_type   = sanitize_input($_POST['account_type'] ?? '');
$initial_deposit = floatval($_POST['initial_deposit'] ?? 0);
$txn_pin         = $_POST['txn_pin'] ?? '';
$confirm_pin     = $_POST['confirm_pin'] ?? '';

if (empty($account_type) || !in_array($account_type, ['savings', 'current'])) {
    $errors['account_type'] = 'Please select a valid account type';
}

if ($initial_deposit < ($min_deposit = get_setting($pdo, 'min_initial_deposit', 500))) {
    $errors['initial_deposit'] = 'Initial deposit must be at least ' . CURRENCY_SYMBOL . $min_deposit;
}

if (empty($txn_pin) || !preg_match('/^\d{4}$/', $txn_pin)) {
    $errors['txn_pin'] = 'Transaction PIN must be exactly 4 digits';
}

if ($txn_pin !== $confirm_pin) {
    $errors['confirm_pin'] = 'PINs do not match';
}

// =====================================================
// RETURN ERRORS IF ANY
// =====================================================
if (!empty($errors)) {
    error_log("KYC Validation Errors: " . print_r($errors, true));
    json_response(['success' => false, 'errors' => $errors], 422);
}

// =====================================================
// PROCESS FILE UPLOADS
// =====================================================

// Create upload directory if it doesn't exist
if (!is_dir(UPLOAD_ID_DOCUMENTS_PATH)) {
    mkdir(UPLOAD_ID_DOCUMENTS_PATH, 0755, true);
}

$front_ext = pathinfo($_FILES['id_front']['name'], PATHINFO_EXTENSION);
$back_ext  = pathinfo($_FILES['id_back']['name'], PATHINFO_EXTENSION);

$front_filename = 'id_front_' . $user_id . '_' . time() . '.' . $front_ext;
$back_filename  = 'id_back_' . $user_id . '_' . time() . '.' . $back_ext;

$front_destination = UPLOAD_ID_DOCUMENTS_PATH . $front_filename;
$back_destination  = UPLOAD_ID_DOCUMENTS_PATH . $back_filename;

if (!move_uploaded_file($_FILES['id_front']['tmp_name'], $front_destination)) {
    json_response(['success' => false, 'message' => 'Failed to upload front document image'], 500);
}

if (!move_uploaded_file($_FILES['id_back']['tmp_name'], $back_destination)) {
    // Clean up front image if back fails
    unlink($front_destination);
    json_response(['success' => false, 'message' => 'Failed to upload back document image'], 500);
}

$id_front_path = 'uploads/id-documents/' . $front_filename;
$id_back_path  = 'uploads/id-documents/' . $back_filename;

// =====================================================
// DATABASE TRANSACTION
// =====================================================

try {
    $pdo->beginTransaction();

    // Update users table with KYC data
    $stmt = $pdo->prepare("
        UPDATE users SET
            dob = ?, gender = ?, father_name = ?, occupation = ?, annual_income = ?,
            street = ?, city = ?, state = ?, pincode = ?,
            id_type = ?, id_number = ?, id_front_path = ?, id_back_path = ?,
            status = 'under_review', kyc_submitted_at = NOW(), rejection_reason = NULL,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $dob, $gender, $father_name, $occupation, $annual_income,
        $street, $city, $state, $pincode,
        $id_type, $id_number, $id_front_path, $id_back_path,
        $user_id
    ]);

    // Hash the transaction PIN
    $txn_pin_hash = password_hash($txn_pin, PASSWORD_BCRYPT);

    // Create account record (pending status until admin approves)
    // Check if account already exists for resubmission
    $stmt = $pdo->prepare("SELECT id FROM accounts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existing_account = $stmt->fetch();

    if ($existing_account) {
        // Update existing account (resubmission case)
        $stmt = $pdo->prepare("
            UPDATE accounts SET
                account_type = ?, balance = ?, txn_pin_hash = ?, status = 'pending', updated_at = NOW()
            WHERE user_id = ? AND status IN ('pending', 'rejected')
        ");
        $stmt->execute([$account_type, $initial_deposit, $txn_pin_hash, $user_id]);
    } else {
        // Create new account record
        $stmt = $pdo->prepare("
            INSERT INTO accounts (user_id, account_no, account_type, balance, txn_pin_hash, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        // Temporary account number — will be replaced with proper one on approval
        $temp_account_no = 'PENDING_' . $user_id;
        $stmt->execute([$user_id, $temp_account_no, $account_type, $initial_deposit, $txn_pin_hash]);
    }

    // Generate unique application number
    $stmt = $pdo->query("SELECT MAX(id) FROM kyc_applications");
    $max_id = $stmt->fetchColumn() ?: 0;
    $next_num = $max_id + 1;
    $year = date('Y');
    $application_no = 'KYC' . $year . str_pad($next_num, 7, '0', STR_PAD_LEFT);

    // Check if user already has a kyc_applications row
    $stmt = $pdo->prepare("SELECT id, submission_count FROM kyc_applications WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existing_app = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_app) {
        // Resubmission
        $new_count = $existing_app['submission_count'] + 1;
        $stmt = $pdo->prepare("
            UPDATE kyc_applications SET
                status = 'submitted',
                submission_count = ?,
                dob = ?,
                gender = ?,
                father_name = ?,
                occupation = ?,
                annual_income = ?,
                street = ?,
                city = ?,
                state = ?,
                pincode = ?,
                id_type = ?,
                id_number = ?,
                id_front_path = ?,
                id_back_path = ?,
                account_type = ?,
                initial_deposit = ?,
                submitted_at = NOW(),
                rejection_reason = NULL,
                reviewed_by = NULL,
                reviewed_at = NULL
            WHERE user_id = ?
        ");
        $stmt->execute([
            $new_count,
            $dob, $gender, $father_name, $occupation, $annual_income,
            $street, $city, $state, $pincode,
            $id_type, $id_number, $id_front_path, $id_back_path,
            $account_type, $initial_deposit,
            $user_id
        ]);
    } else {
        // First submission
        $stmt = $pdo->prepare("
            INSERT INTO kyc_applications (
                user_id, application_no, status, submission_count,
                dob, gender, father_name, occupation, annual_income,
                street, city, state, pincode,
                id_type, id_number, id_front_path, id_back_path,
                account_type, initial_deposit, submitted_at, created_at, updated_at
            ) VALUES (
                ?, ?, 'submitted', 1,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, NOW(), NOW(), NOW()
            )
        ");
        $stmt->execute([
            $user_id, $application_no,
            $dob, $gender, $father_name, $occupation, $annual_income,
            $street, $city, $state, $pincode,
            $id_type, $id_number, $id_front_path, $id_back_path,
            $account_type, $initial_deposit
        ]);
    }

    $pdo->commit();

    // Update session status
    $_SESSION['user_status'] = 'under_review';

    // Send under-review email
    send_under_review_email($_SESSION['user_email'], $_SESSION['user_name']);

    // Send in-app notification: Application Submitted
    send_notification(
        $pdo,
        $user_id,
        'system',
        'Application Submitted for Review',
        'Your KYC profile has been successfully submitted. Our team will review your documents and notify you within 24 hours. You can track the status from your dashboard.'
    );

    json_response([
        'success'  => true,
        'message'  => 'Your profile has been submitted for review. We will notify you once it is processed.',
        'redirect' => '/views/user/dashboard.html'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();

    // Clean up uploaded files on failure
    if (file_exists($front_destination)) unlink($front_destination);
    if (file_exists($back_destination)) unlink($back_destination);

    error_log("KYC submission error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to submit profile. Please try again.'], 500);
}
