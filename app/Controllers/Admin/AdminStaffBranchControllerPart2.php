<?php
namespace App\Controllers\Admin;

use App\Services\AdminStaffBranchService;
use Exception;
use PDOException;

class AdminStaffBranchControllerPart2 {
    protected AdminStaffBranchService $service;

    public function __construct(AdminStaffBranchService $service) {
        $this->service = $service;
    }

    public function uploadStaffPhoto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        $staff_id = intval($_POST['staff_id'] ?? 0);
        if (!$staff_id) json_response(['success' => false, 'message' => 'Staff ID is required'], 422);

        try {
            $staff = $this->service->getRepo()->findStaffRecord($staff_id);
            if (!$staff) json_response(['success' => false, 'message' => 'Staff member not found'], 404);
            
            if (empty($_FILES['photo']['name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) json_response(['success' => false, 'message' => 'Photo file is required'], 422);
            $file = $_FILES['photo'];
            if (!in_array($file['type'], ['image/jpeg', 'image/png'])) json_response(['success' => false, 'message' => 'Only JPG and PNG images are allowed'], 422);
            if ($file['size'] > 5242880) json_response(['success' => false, 'message' => 'File size exceeds 5MB limit'], 422);

            if ($staff['profile_photo']) {
                $old_path = dirname(__DIR__, 3) . '/' . $staff['profile_photo'];
                if (file_exists($old_path)) @unlink($old_path);
            }
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'staff_photo_' . $staff_id . '_' . time() . '.' . $ext;
            $target_dir = dirname(__DIR__, 3) . '/uploads/staff-photos/';
            if (!move_uploaded_file($file['tmp_name'], $target_dir . $filename)) json_response(['success' => false, 'message' => 'Failed to save uploaded file'], 500);

            $new_photo_path = 'uploads/staff-photos/' . $filename;
            $this->service->getRepo()->updateStaffPhoto($staff_id, $new_photo_path);
            
            json_response(['success' => true, 'message' => 'Photo updated successfully', 'photo_url' => APP_URL . '/' . $new_photo_path]);
        } catch (PDOException $e) {
            json_response(['success' => false, 'message' => 'Failed to upload photo'], 500);
        }
    }
}
