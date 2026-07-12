<?php
/**
 * Finzo Banking System — Upload Admin Photo API
 * POST endpoint: allows any admin to update their own profile picture.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    json_response(['success' => false, 'message' => 'No valid file uploaded'], 422);
}

$file = $_FILES['profile_photo'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['jpg', 'jpeg', 'png']) || !in_array($file['type'], ['image/jpeg', 'image/png'])) {
    json_response(['success' => false, 'message' => 'Profile photo must be JPG or PNG'], 422);
}

if ($file['size'] > 10 * 1024 * 1024) {
    json_response(['success' => false, 'message' => 'Profile photo max size is 10MB'], 422);
}

$admin_id = $_SESSION['admin_id'];

try {
    // Check if distinct old photo exists
    $stmt = $pdo->prepare("SELECT profile_photo FROM admin_users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $old_photo = $stmt->fetchColumn();

    if ($old_photo) {
        $old_path = dirname(__DIR__, 3) . $old_photo;
        if (file_exists($old_path)) {
            unlink($old_path);
        }
    }

    $upload_dir = dirname(__DIR__, 3) . '/uploads/admin-photos/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    
    $filename = 'admin_' . $admin_id . '_' . time() . '.' . $ext;
    $dest = $upload_dir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        json_response(['success' => false, 'message' => 'Failed to save profile photo'], 500);
    }
    
    $photo_path = '/uploads/admin-photos/' . $filename;

    // Update the DB
    $stmt = $pdo->prepare("UPDATE admin_users SET profile_photo = ? WHERE id = ?");
    $stmt->execute([$photo_path, $admin_id]);

    json_response(['success' => true, 'photo_url' => $photo_path]);

} catch (PDOException $e) {
    error_log("Upload Admin Photo Error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to update photo.'], 500);
}
