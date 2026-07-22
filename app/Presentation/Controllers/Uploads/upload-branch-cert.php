<?php

/**
 * Finzo Banking System — Upload Branch Certificate Handler
 * POST endpoint: handles branch certificate file uploads.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/Presentation/Middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Check for file
if (!\App\Shared\Core\Validation\Validator::isSet($_FILES['certificate']) || $_FILES['certificate']['error'] !== UPLOAD_ERR_OK) {
    json_response(['success' => false, 'message' => 'No certificate file uploaded'], 400);
}

$file = $_FILES['certificate'];
$branch_id = intval($_POST['branch_id'] ?? 0);

// Validate file size
if ($file['size'] > MAX_UPLOAD_SIZE) {
    json_response(['success' => false, 'message' => 'File size must be less than 5MB'], 422);
}

// Allow images and PDFs for certificates
$allowed_types = array_merge(ALLOWED_IMAGE_TYPES, ['application/pdf']);

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed_types)) {
    json_response(['success' => false, 'message' => 'Only JPG, PNG, and PDF files are allowed'], 422);
}

// Create upload directory
if (!is_dir(UPLOAD_BRANCH_CERTS_PATH)) {
    mkdir(UPLOAD_BRANCH_CERTS_PATH, 0755, true);
}

// Generate filename securely based on real MIME type
$ext_map = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'application/pdf' => 'pdf'
];
$ext = $ext_map[$mime] ?? 'bin';
$filename = 'branch_cert_' . ($branch_id ?: 'new') . '_' . time() . '.' . $ext;
$destination = UPLOAD_BRANCH_CERTS_PATH . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    json_response(['success' => false, 'message' => 'Failed to save certificate file'], 500);
}

$relative_path = 'uploads/branch-certs/' . $filename;

// Update branch record if branch_id provided
if ($branch_id > 0) {
    try {
        $stmt = $pdo->prepare("UPDATE branches SET cert_path = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$relative_path, $branch_id]);
    } catch (PDOException $e) {
        error_log("Branch cert upload DB error: " . $e->getMessage());
    }
}

json_response([
    'success'  => true,
    'message'  => 'Certificate uploaded successfully',
    'path'     => $relative_path,
    'filename' => $filename
]);
