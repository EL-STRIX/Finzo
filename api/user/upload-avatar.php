<?php
/**
 * Finzo Banking System — Customer Avatar Upload API
 * POST: upload profile photo
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-user.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    json_response(['success' => false, 'message' => 'No file uploaded or upload error'], 400);
}

$user_id = $_SESSION['user_id'];
$upload_dir = dirname(__DIR__, 2) . '/uploads/avatars/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file = $_FILES['avatar'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif'];

if (!in_array($ext, $allowed)) {
    json_response(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF allowed.'], 400);
}

// Always save as JPG for predictability or keep original. We will use .jpg.
$dest_path = $upload_dir . 'user_' . $user_id . '.jpg';

if (move_uploaded_file($file['tmp_name'], $dest_path)) {
    json_response([
        'success' => true, 
        'message' => 'Avatar updated', 
        'avatar_url' => '../../uploads/avatars/user_' . $user_id . '.jpg?t=' . time()
    ]);
} else {
    json_response(['success' => false, 'message' => 'Failed to save file'], 500);
}
