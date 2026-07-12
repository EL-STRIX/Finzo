<?php
namespace App\Controllers\Admin;

use App\Services\AdminManagementService;
use Exception;

class AdminManagementController {
    
    private AdminManagementService $service;

    public function __construct(AdminManagementService $service) {
        $this->service = $service;
    }
    
    public function addAdmin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $this->service->validateCsrf($_POST['csrf_token'] ?? '');
            $this->service->requireSuperAdmin();
            
            $file = (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) ? $_FILES['profile_photo'] : null;
            $result = $this->service->addAdmin($_POST, $file);
            
            $code = $result['code'] ?? 200;
            unset($result['code']);
            json_response($result, $code);
            
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            if ($code === 500) {
                error_log("Add Admin Error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'A system error occurred. Please try again.'], 500);
            }
            json_response(['success' => false, 'message' => $e->getMessage()], $code);
        }
    }

    public function adminAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $this->service->validateCsrf($_POST['csrf_token'] ?? '');
            $this->service->requireSuperAdmin();
            
            $action = clean_input($_POST['action'] ?? '');
            $target_id = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : 0;
            
            $result = $this->service->handleAdminAction($action, $target_id);
            $code = $result['code'] ?? 200;
            unset($result['code']);
            json_response($result, $code);
            
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            if ($code === 500) {
                error_log("Admin Action Error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'Database error during administrative action.'], 500);
            }
            json_response(['success' => false, 'message' => $e->getMessage()], $code);
        }
    }

    public function getAdminDetail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $this->service->requireSuperAdmin();
            
            $target_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $result = $this->service->getAdminDetail($target_id);
            json_response($result);
            
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            if ($code === 500) {
                error_log("Get Admin Detail Error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'Failed to load admin details.'], 500);
            }
            json_response(['success' => false, 'message' => $e->getMessage()], $code);
        }
    }

    public function getAdmins() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $this->service->requireSuperAdmin();
            $result = $this->service->getAdmins();
            json_response($result);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            if ($code === 500) {
                error_log("Get Admins Error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'Failed to retrieve admins.'], 500);
            }
            json_response(['success' => false, 'message' => 'Forbidden'], 403);
        }
    }

    public function getLockedAdmins() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
                json_response(['success' => false, 'message' => 'Unauthorized. Super Admin access required.'], 403);
            }
            $result = $this->service->getLockedAdmins();
            json_response($result);
        } catch (Exception $e) {
            json_response(['success' => false, 'message' => 'Failed to retrieve locked admins.'], 500);
        }
    }

    public function getNextAdminId() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        try {
            if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
                json_response(['success' => false, 'message' => 'Forbidden'], 403);
            }
            $next_id = $this->service->getNextAdminId();
            json_response(['success' => true, 'next_id' => $next_id]);
        } catch (Exception $e) {
            error_log("Get Next Admin ID Error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to calculate next ID'], 500);
        }
    }

    public function profile() {
        $admin_id = $_SESSION['admin_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                $result = $this->service->getProfileData($admin_id);
                json_response($result);
            } catch (Exception $e) {
                $code = $e->getCode() ?: 500;
                if ($code === 500) {
                    error_log("Admin profile GET error: " . $e->getMessage());
                    json_response(['success' => false, 'message' => 'Failed to load admin profile'], 500);
                }
                json_response(['success' => false, 'message' => $e->getMessage()], $code);
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = clean_input($_POST['action'] ?? 'update_profile');
            
            try {
                if ($action === 'change_password') {
                    $result = $this->service->changePassword($admin_id, $_POST);
                } elseif ($action === 'upload_avatar') {
                    $file = $_FILES['avatar'] ?? null;
                    $result = $this->service->uploadAvatar($admin_id, $file, true);
                } else {
                    $result = $this->service->updateProfile($admin_id, $_POST);
                }
                
                $code = $result['code'] ?? 200;
                unset($result['code']);
                json_response($result, $code);
                
            } catch (Exception $e) {
                $code = $e->getCode() ?: 500;
                if ($code === 500) {
                    error_log("Admin profile update error: " . $e->getMessage());
                    json_response(['success' => false, 'message' => 'Failed to update profile'], 500);
                }
                json_response(['success' => false, 'message' => $e->getMessage()], $code);
            }
        }

        json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }
    
    public function updateAdminProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $is_json = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
        if ($is_json) {
            $json_input = json_decode(file_get_contents('php://input'), true);
            if ($json_input !== null) {
                $_POST = array_merge($_POST, $json_input);
            }
        }

        if (isset($_POST['two_fa_enabled']) && count($_POST) <= 2) {
            try {
                $enable = intval($_POST['two_fa_enabled']);
                $result = $this->service->updateTwoFaSettings($_SESSION['admin_id'], $enable);
                $code = $result['code'] ?? 200;
                unset($result['code']);
                json_response($result, $code);
            } catch (Exception $e) {
                json_response(['success' => false, 'message' => 'Failed to update 2FA'], 500);
            }
        }
        
        try {
            $this->service->validateCsrf($_POST['csrf_token'] ?? '');
            
            $result = $this->service->updateAdminProfile($_SESSION['admin_id'], $_POST);
            $code = $result['code'] ?? 200;
            unset($result['code']);
            json_response($result, $code);
            
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            if ($code === 500) {
                error_log("Update Admin Profile Error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'Failed to update profile. Please try again.'], 500);
            }
            json_response(['success' => false, 'message' => $e->getMessage()], $code);
        }
    }
    
    public function uploadAdminPhoto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $file = $_FILES['profile_photo'] ?? null;
            $result = $this->service->uploadAvatar($_SESSION['admin_id'], $file, false);
            $code = $result['code'] ?? 200;
            unset($result['code']);
            json_response($result, $code);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            if ($code === 500) {
                error_log("Upload Admin Photo Error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'Failed to update photo.'], 500);
            }
            json_response(['success' => false, 'message' => $e->getMessage()], $code);
        }
    }
    
    public function getAdminProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        try {
            $result = $this->service->getAdminProfileForUser($_SESSION['admin_id']);
            json_response($result);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            if ($code === 500) {
                error_log("Get Admin Profile Error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'Failed to load profile'], 500);
            }
            json_response(['success' => false, 'message' => $e->getMessage()], $code);
        }
    }
}
