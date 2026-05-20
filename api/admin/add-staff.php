<?php
/**
 * api/admin/add-staff.php
 * POST endpoint: Creates a new staff account with address and documents.
 * Handles multi-table insertion using PDO transaction.
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$admin_id = $_SESSION['admin_id'];

// 1. Validate CSRF token
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrf_token)) {
    json_response(['success' => false, 'message' => 'Invalid CSRF token'], 403);
}

// 2. Check admin permission
check_admin_permission($pdo, $admin_id, 'add_staff');

// 3. Collect and Sanitize Inputs
$full_name               = sanitize_input($_POST['full_name'] ?? '');
$date_of_birth           = sanitize_input($_POST['date_of_birth'] ?? '');
$gender                  = sanitize_input($_POST['gender'] ?? '');
$personal_email          = sanitize_input($_POST['personal_email'] ?? '');
$phone                   = sanitize_input($_POST['phone'] ?? '');
$emergency_contact_name  = sanitize_input($_POST['emergency_contact_name'] ?? '');
$emergency_contact_phone = sanitize_input($_POST['emergency_contact_phone'] ?? '');
$designation             = sanitize_input($_POST['designation'] ?? 'Teller');
$branch_id_raw           = $_POST['branch_id'] ?? '';
$branch_id               = ($branch_id_raw !== '' && $branch_id_raw !== 'null') ? intval($branch_id_raw) : null;
$date_of_joining         = sanitize_input($_POST['date_of_joining'] ?? date('Y-m-d'));
$street                  = sanitize_input($_POST['street'] ?? '');
$city                    = sanitize_input($_POST['city'] ?? '');
$state                   = sanitize_input($_POST['state'] ?? '');
$pincode                 = sanitize_input($_POST['pincode'] ?? '');
$id_type                 = sanitize_input($_POST['id_type'] ?? '');
$id_number               = sanitize_input($_POST['id_number'] ?? '');
$password                = $_POST['password'] ?? '';

// Default values
$department = 'Teller Operations';
if (empty($date_of_joining)) {
    $date_of_joining = date('Y-m-d');
}

$errors = [];

// Validation
if (empty($full_name) || strlen($full_name) < 2 || strlen($full_name) > 150) {
    $errors['full_name'] = 'Full name is required (2-150 chars)';
}

if (empty($date_of_birth)) {
    $errors['date_of_birth'] = 'Date of birth is required';
} else {
    $dob = new DateTime($date_of_birth);
    $today = new DateTime();
    $age = $today->diff($dob)->y;
    if ($age < 18) {
        $errors['date_of_birth'] = 'Staff must be at least 18 years old';
    }
}

if (!in_array($gender, ['Male', 'Female', 'Other', 'Prefer not to say'])) {
    $errors['gender'] = 'Please select a valid gender';
}

if (empty($personal_email) || !filter_var($personal_email, FILTER_VALIDATE_EMAIL)) {
    $errors['personal_email'] = 'Valid personal email is required';
} else {
    // Check uniqueness
    $stmt = $pdo->prepare("SELECT id FROM staff WHERE personal_email = ?");
    $stmt->execute([$personal_email]);
    if ($stmt->fetch()) {
        $errors['personal_email'] = 'This email is already in use by another staff member';
    }
}

if (empty($phone) || !validate_phone($phone)) {
    $errors['phone'] = 'Valid 10-digit mobile number is required';
}

if (!in_array($id_type, ['Aadhaar', 'PAN', 'Passport'])) {
    $errors['id_type'] = 'Please select a valid ID type';
}

if (empty($id_number) || strlen($id_number) < 5 || strlen($id_number) > 20) {
    $errors['id_number'] = 'ID number is required (5-20 chars)';
}

if (!in_array($designation, ['Teller', 'Supervisor', 'Manager'])) {
    $errors['designation'] = 'Please select a valid designation';
}

if ($branch_id) {
    $stmt = $pdo->prepare("SELECT id FROM branches WHERE id = ?");
    $stmt->execute([$branch_id]);
    if (!$stmt->fetch()) {
        $errors['branch_id'] = 'Invalid branch selected';
    }
}

if (empty($password) || strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
}

if (empty($city)) $errors['city'] = 'City is required';
if (empty($state)) $errors['state'] = 'State is required';
if (empty($pincode) || !validate_pincode($pincode)) $errors['pincode'] = 'Valid 6-digit pincode is required';

// File Upload Validation
$profile_photo_path = null;
$id_front_path = null;
$id_back_path = null;

// Helper function for file uploads
function handle_staff_upload($file, $prefix, $dir, &$errors, $field_name, $required = false) {
    // Safely handle null or missing file input
    $hasFile = is_array($file) && !empty($file['name']) && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK;

    if (!$hasFile) {
        if ($required) {
            $errors[$field_name] = 'Please upload a file (JPG or PNG, max 5MB)';
        }
        return null;
    }
    
    $allowed_types = ['image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowed_types)) {
        $errors[$field_name] = 'Only JPG and PNG images are allowed';
        return null;
    }
    
    if ($file['size'] > 5242880) { // 5MB
        $errors[$field_name] = 'File size exceeds 5MB limit';
        return null;
    }
    
    // Ensure upload directory exists
    $target_dir = dirname(__DIR__, 2) . '/uploads/' . $dir . '/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . uniqid() . '.' . $ext;
    
    if (!move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
        $errors[$field_name] = 'Failed to save uploaded file. Please try again.';
        return null;
    }
    
    return 'uploads/' . $dir . '/' . $filename;
}

// ID Documents (Required)
$id_front_file = (isset($_FILES['id_front']) && is_array($_FILES['id_front'])) ? $_FILES['id_front'] : null;
$id_back_file  = (isset($_FILES['id_back'])  && is_array($_FILES['id_back']))  ? $_FILES['id_back']  : null;
$id_front_path = handle_staff_upload($id_front_file, 'staff_doc_front_', 'staff-documents', $errors, 'id_front', true);
$id_back_path  = handle_staff_upload($id_back_file,  'staff_doc_back_',  'staff-documents', $errors, 'id_back',  true);

// Optional photo
$profile_photo_file = (isset($_FILES['profile_photo']) && is_array($_FILES['profile_photo'])) ? $_FILES['profile_photo'] : null;
if ($profile_photo_file && !empty($profile_photo_file['name'])) {
    $profile_photo_path = handle_staff_upload($profile_photo_file, 'staff_photo_', 'staff-photos', $errors, 'profile_photo');
}

if (!empty($errors)) {
    json_response(['success' => false, 'message' => 'Validation failed', 'errors' => $errors], 422);
}

try {
    $pdo->beginTransaction();

    // 7. Generate Staff ID
    $staff_id_str = generate_staff_id($pdo);

    // 8. Hash Password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Fetch creator's alphanumeric admin_id (e.g., STF001)
    $creator_stmt = $pdo->prepare("SELECT admin_id FROM admin_users WHERE id = ?");
    $creator_stmt->execute([$admin_id]);
    $creator_admin_id = $creator_stmt->fetchColumn() ?: null;

    // 12. Insert into staff table
    $stmt = $pdo->prepare("
        INSERT INTO staff (
            full_name, date_of_birth, gender, profile_photo,
            personal_email, phone, emergency_contact_name, emergency_contact_phone,
            staff_id, designation, department, branch_id,
            date_of_joining, password_hash, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)
    ");
    $stmt->execute([
        $full_name, $date_of_birth, $gender, $profile_photo_path,
        $personal_email, $phone, $emergency_contact_name, $emergency_contact_phone,
        $staff_id_str, $designation, $department, $branch_id,
        $date_of_joining, $password_hash, $creator_admin_id
    ]);
    
    $new_staff_db_id = $pdo->lastInsertId();

    // 13. Insert into staff_documents (uses integer FK to staff.id)
    $stmt = $pdo->prepare("
        INSERT INTO staff_documents (staff_id, id_type, id_number, front_image, back_image)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$new_staff_db_id, $id_type, $id_number, $id_front_path, $id_back_path]);

    // 14. Insert into staff_address (uses integer FK to staff.id)
    $stmt = $pdo->prepare("
        INSERT INTO staff_address (staff_id, street, city, state, pincode, country)
        VALUES (?, ?, ?, ?, ?, 'India')
    ");
    $stmt->execute([$new_staff_db_id, $street, $city, $state, $pincode]);

    $pdo->commit();

    // 16. Write Audit Log
    write_audit_log($pdo, 'staff_created', $new_staff_db_id, 'staff', $admin_id, 'admin', 
        "Staff created: $full_name | ID: $staff_id_str | Branch: " . ($branch_id ?? 'None'));

    json_response([
        'success' => true,
        'message' => 'Staff member added successfully',
        'staff_id' => $staff_id_str,
        'staff_name' => $full_name
    ], 201);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Clean up uploaded files on failure
    if ($profile_photo_path) @unlink(dirname(__DIR__, 2) . '/' . $profile_photo_path);
    if ($id_front_path) @unlink(dirname(__DIR__, 2) . '/' . $id_front_path);
    if ($id_back_path) @unlink(dirname(__DIR__, 2) . '/' . $id_back_path);
    
    error_log("Add staff error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'An error occurred while creating staff account'], 500);
}
