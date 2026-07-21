<?php

/**
 * Finzo Banking System — Upload ID Document Handler
 * POST endpoint: handles identity document image uploads.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/Presentation/Middleware/auth-user.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$user_id = $_SESSION['user_id'];

// Check for file
if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
    ];
    $error_code = $_FILES['document']['error'] ?? UPLOAD_ERR_NO_FILE;
    $message = $error_messages[$error_code] ?? 'Unknown upload error';
    json_response(['success' => false, 'message' => $message], 400);
}

$file = $_FILES['document'];
$side = $this->security->cleanInput($_POST['side'] ?? 'front'); // 'front' or 'back'

// Validate file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
    json_response(['success' => false, 'message' => 'Only JPG and PNG images are allowed'], 422);
}

// Validate file size
if ($file['size'] > MAX_UPLOAD_SIZE) {
    json_response(['success' => false, 'message' => 'File size must be less than 5MB'], 422);
}

// Validate side parameter
if (!in_array($side, ['front', 'back'])) {
    json_response(['success' => false, 'message' => 'Invalid document side'], 422);
}

// Create upload directory if needed
if (!is_dir(UPLOAD_ID_DOCUMENTS_PATH)) {
    mkdir(UPLOAD_ID_DOCUMENTS_PATH, 0755, true);
}

// Generate unique filename securely based on real MIME type
$ext = ($mime === 'image/jpeg') ? 'jpg' : 'png';
$filename = "id_{$side}_{$user_id}_" . time() . '.' . $ext;
$destination = UPLOAD_ID_DOCUMENTS_PATH . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    json_response(['success' => false, 'message' => 'Failed to save uploaded file'], 500);
}

// Relative path for database storage
$relative_path = 'uploads/id-documents/' . $filename;

// Update user record with the document path
try {
    $column = ($side === 'front') ? 'id_front_path' : 'id_back_path';
    $stmt = $pdo->prepare("UPDATE users SET {$column} = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$relative_path, $user_id]);

    json_response([
        'success'  => true,
        'message'  => ucfirst($side) . ' document uploaded successfully',
        'path'     => $relative_path,
        'filename' => $filename
    ]);
} catch (PDOException $e) {
    // Clean up file on DB failure
    unlink($destination);
    error_log("ID document upload DB error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to save document reference'], 500);
}
