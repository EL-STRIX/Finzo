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
$cid_stmt = $pdo->prepare("SELECT customer_id FROM users WHERE id = ?");
$cid_stmt->execute([$user_id]);
$customer_id_str = $cid_stmt->fetchColumn();
$errors = [];

// =====================================================
// STEP 1: PERSONAL INFO VALIDATION
// =====================================================
$dob              = clean_input($_POST['dob'] ?? '');
$gender           = clean_input($_POST['gender'] ?? '');
$father_name      = clean_input($_POST['father_name'] ?? '');
$mother_name      = clean_input($_POST['mother_name'] ?? '');
$marital_status   = clean_input($_POST['marital_status'] ?? '');
$nationality      = clean_input($_POST['nationality'] ?? '');
$occupation       = clean_input($_POST['occupation'] ?? '');
$annual_income    = clean_input($_POST['annual_income'] ?? '');
$source_of_income = clean_input($_POST['source_of_income'] ?? '');

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

if (empty($mother_name) || strlen($mother_name) < 2) {
    $errors['mother_name'] = "Mother's name is required";
}

if (empty($marital_status) || !in_array($marital_status, ['Single', 'Married', 'Divorced', 'Widowed', 'Prefer not to say'])) {
    $errors['marital_status'] = 'Please select a valid marital status';
}

if (empty($nationality)) {
    $errors['nationality'] = 'Nationality is required';
}

if (empty($occupation)) {
    $errors['occupation'] = 'Occupation is required';
}

if (empty($source_of_income)) {
    $errors['source_of_income'] = 'Source of income is required';
}

if (empty($annual_income)) {
    $errors['annual_income'] = 'Annual income range is required';
}

// =====================================================
// STEP 2: ADDRESS VALIDATION
// =====================================================
$street  = clean_input($_POST['street'] ?? '');
$city    = clean_input($_POST['city'] ?? '');
$state   = clean_input($_POST['state'] ?? '');
$pincode = clean_input($_POST['pincode'] ?? '');

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
$id_type   = clean_input($_POST['id_type'] ?? '');
$id_number = clean_input($_POST['id_number'] ?? '');

$valid_id_types = ['Aadhaar', 'PAN', 'Passport', 'Voter ID', 'Driving License'];

if (empty($id_type) || !in_array($id_type, $valid_id_types)) {
    $errors['id_type'] = 'Please select a valid document type';
}

if (empty($id_number)) {
    $errors['id_number'] = 'Document number is required';
}

// Fetch existing user to check if files already exist
$stmt = $pdo->prepare("SELECT profile_picture, id_front_path, id_back_path FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$existing_user = $stmt->fetch();

$has_profile_pic = !empty($existing_user['profile_picture']);
$has_id_front = !empty($existing_user['id_front_path']);
$has_id_back = !empty($existing_user['id_back_path']);

// Validate file uploads
$id_front_path = '';
$id_back_path  = '';
$profile_pic_path = '';

if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    if (!$has_profile_pic) {
        $errors['profile_picture'] = 'Profile photo is required';
    }
} else {
    $profile = $_FILES['profile_picture'];
    if ($profile['size'] > MAX_UPLOAD_SIZE) {
        $errors['profile_picture'] = 'Profile photo must be less than 5MB';
    }
    if (!in_array($profile['type'], ALLOWED_IMAGE_TYPES)) {
        $errors['profile_picture'] = 'Profile photo must be JPG or PNG format';
    }
}

if (!isset($_FILES['id_front']) || $_FILES['id_front']['error'] !== UPLOAD_ERR_OK) {
    if (!$has_id_front) {
        $errors['id_front'] = 'Front image of identity document is required';
    }
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
    if (!$has_id_back) {
        $errors['id_back'] = 'Back image of identity document is required';
    }
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
$account_type   = clean_input($_POST['account_type'] ?? '');
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

// Create profile uploads directory if it doesn't exist
$profile_upload_path = dirname(__DIR__, 2) . '/uploads/profiles/';
if (!is_dir($profile_upload_path)) {
    mkdir($profile_upload_path, 0755, true);
}

$profile_ext = isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK ? pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION) : '';
$front_ext = isset($_FILES['id_front']) && $_FILES['id_front']['error'] === UPLOAD_ERR_OK ? pathinfo($_FILES['id_front']['name'], PATHINFO_EXTENSION) : '';
$back_ext  = isset($_FILES['id_back']) && $_FILES['id_back']['error'] === UPLOAD_ERR_OK ? pathinfo($_FILES['id_back']['name'], PATHINFO_EXTENSION) : '';

$profile_filename = $profile_ext ? 'profile_' . $user_id . '_' . time() . '.' . $profile_ext : '';
$front_filename = $front_ext ? 'id_front_' . $user_id . '_' . time() . '.' . $front_ext : '';
$back_filename  = $back_ext ? 'id_back_' . $user_id . '_' . time() . '.' . $back_ext : '';

$profile_destination = $profile_filename ? $profile_upload_path . $profile_filename : '';
$front_destination = $front_filename ? UPLOAD_ID_DOCUMENTS_PATH . $front_filename : '';
$back_destination  = $back_filename ? UPLOAD_ID_DOCUMENTS_PATH . $back_filename : '';

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_destination)) {
        json_response(['success' => false, 'message' => 'Failed to upload profile photo'], 500);
    }
}

if (isset($_FILES['id_front']) && $_FILES['id_front']['error'] === UPLOAD_ERR_OK) {
    if (!move_uploaded_file($_FILES['id_front']['tmp_name'], $front_destination)) {
        json_response(['success' => false, 'message' => 'Failed to upload front document image'], 500);
    }
}

if (isset($_FILES['id_back']) && $_FILES['id_back']['error'] === UPLOAD_ERR_OK) {
    if (!move_uploaded_file($_FILES['id_back']['tmp_name'], $back_destination)) {
        json_response(['success' => false, 'message' => 'Failed to upload back document image'], 500);
    }
}

$profile_pic_path = (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) 
    ? 'uploads/profiles/' . $profile_filename 
    : $existing_user['profile_picture'];

$id_front_path = (isset($_FILES['id_front']) && $_FILES['id_front']['error'] === UPLOAD_ERR_OK) 
    ? 'uploads/id-documents/' . $front_filename 
    : $existing_user['id_front_path'];

$id_back_path = (isset($_FILES['id_back']) && $_FILES['id_back']['error'] === UPLOAD_ERR_OK) 
    ? 'uploads/id-documents/' . $back_filename 
    : $existing_user['id_back_path'];

// =====================================================
// DATABASE TRANSACTION
// =====================================================

try {
    $pdo->beginTransaction();

    // Update users table with KYC data
    $stmt = $pdo->prepare("
        UPDATE users SET
            profile_picture = ?, dob = ?, gender = ?, father_name = ?, mother_name = ?, marital_status = ?, nationality = ?, occupation = ?, annual_income = ?, source_of_income = ?,
            street = ?, city = ?, state = ?, pincode = ?,
            id_type = ?, id_number = ?, id_front_path = ?, id_back_path = ?,
            status = 'under_review', kyc_submitted_at = NOW(), rejection_reason = NULL,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $profile_pic_path, $dob, $gender, $father_name, $mother_name, $marital_status, $nationality, $occupation, $annual_income, $source_of_income,
        $street, $city, $state, $pincode,
        $id_type, $id_number, $id_front_path, $id_back_path,
        $user_id
    ]);

    // Hash the transaction PIN
    $txn_pin_hash = password_hash($txn_pin, PASSWORD_BCRYPT);

    // Fetch customer_id to use in accounts table
    $stmt = $pdo->prepare("SELECT customer_id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $customer_id = $stmt->fetchColumn();

    // Create account record (pending status until admin approves)
    // Check if account already exists for resubmission
    $stmt = $pdo->prepare("SELECT id FROM accounts WHERE user_id = ?");
    $stmt->execute([$customer_id]);
    $existing_account = $stmt->fetch();

    if ($existing_account) {
        // Update existing account (resubmission case)
        $stmt = $pdo->prepare("
            UPDATE accounts SET
                account_type = ?, balance = ?, txn_pin_hash = ?, status = 'pending', updated_at = NOW()
            WHERE user_id = ? AND status IN ('pending', 'rejected')
        ");
        $stmt->execute([$account_type, $initial_deposit, $txn_pin_hash, $customer_id]);
    } else {
        // Create new account record
        $stmt = $pdo->prepare("
            INSERT INTO accounts (user_id, account_no, account_type, balance, txn_pin_hash, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        // Temporary account number — will be replaced with proper one on approval
        $temp_account_no = 'PENDING_' . $user_id;
        $stmt->execute([$customer_id, $temp_account_no, $account_type, $initial_deposit, $txn_pin_hash]);
    }

    // Generate unique application number
    $year = date('Y');
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(application_no, 8) AS UNSIGNED)) FROM kyc_applications WHERE application_no LIKE 'KYC{$year}%'");
    $next_num = ($stmt->fetchColumn() ?: 0) + 1;
    $application_no = 'KYC' . $year . str_pad($next_num, 7, '0', STR_PAD_LEFT);

    // Check if user already has a kyc_applications row
    $stmt = $pdo->prepare("SELECT id, submission_count FROM kyc_applications WHERE user_id = ?");
    $stmt->execute([$customer_id_str]);
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
            $customer_id_str
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
            $customer_id_str, $application_no,
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
        $customer_id,
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
