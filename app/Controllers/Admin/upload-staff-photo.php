<?php
/**
 * api/admin/upload-staff-photo.php
 * POST endpoint: Uploads and updates a staff member's profile photo.
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$admin_id = $_SESSION['admin_id'];
$staff_id = intval($_POST['staff_id'] ?? 0);

if (!$staff_id) {
    json_response(['success' => false, 'message' => 'Staff ID is required'], 422);
}

try {
    // Check if staff exists
    $stmt = $pdo->prepare("SELECT id, profile_photo FROM staff WHERE id = ?");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch();

    if (!$staff) {
        json_response(['success' => false, 'message' => 'Staff member not found'], 404);
    }

    if (empty($_FILES['photo']['name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        json_response(['success' => false, 'message' => 'Photo file is required'], 422);
    }

    $file = $_FILES['photo'];
    $allowed_types = ['image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowed_types)) {
        json_response(['success' => false, 'message' => 'Only JPG and PNG images are allowed'], 422);
    }

    if ($file['size'] > 5242880) {
        json_response(['success' => false, 'message' => 'File size exceeds 5MB limit'], 422);
    }

    // 2. Get current photo path and unlink if exists
    if ($staff['profile_photo']) {
        $old_path = dirname(__DIR__, 3) . '/' . $staff['profile_photo'];
        if (file_exists($old_path)) {
            @unlink($old_path);
        }
    }

    // 3. Save new file
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'staff_photo_' . $staff_id . '_' . time() . '.' . $ext;
    $target_dir = dirname(__DIR__, 3) . '/uploads/staff-photos/';
    
    if (!move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
        json_response(['success' => false, 'message' => 'Failed to save uploaded file'], 500);
    }

    $new_photo_path = 'uploads/staff-photos/' . $filename;

    // 4. Update staff table
    $stmt = $pdo->prepare("UPDATE staff SET profile_photo = ? WHERE id = ?");
    $stmt->execute([$new_photo_path, $staff_id]);

    json_response([
        'success' => true,
        'message' => 'Photo updated successfully',
        'photo_url' => APP_URL . '/' . $new_photo_path
    ]);

} catch (PDOException $e) {
    error_log("Upload staff photo error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to upload photo'], 500);
}
