<?php

namespace App\Controllers\Admin;

use App\Services\AdminManagementService;
use App\Core\Exceptions\ApiException;
use Exception;

class AdminManagementController
{
    private AdminManagementService $service;

    public function __construct(AdminManagementService $service)
    {
        $this->service = $service;
    }

    public function addAdmin(): array
    {
        $this->service->validateCsrf($_POST['csrf_token'] ?? '');
        $this->service->requireSuperAdmin();

        $file = (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) ? $_FILES['profile_photo'] : null;
        $result = $this->service->addAdmin($_POST, $file);

        $code = $result['code'] ?? 200;
        unset($result['code']);

        if ($code === 201) {
            json_response($result, 201);
        }

        return $result;
    }

    public function adminAction(): array
    {
        $this->service->validateCsrf($_POST['csrf_token'] ?? '');
        $this->service->requireSuperAdmin();

        $action = clean_input($_POST['action'] ?? '');
        $target_id = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : 0;

        $result = $this->service->handleAdminAction($action, $target_id);
        unset($result['code']);
        return $result;
    }

    public function getAdminDetail(): array
    {
        $this->service->requireSuperAdmin();

        $target_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        return $this->service->getAdminDetail($target_id);
    }

    public function getAdmins(): array
    {
        $this->service->requireSuperAdmin();
        return $this->service->getAdmins();
    }

    public function getLockedAdmins(): array
    {
        if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
            throw new ApiException('Unauthorized. Super Admin access required.', 403);
        }
        return $this->service->getLockedAdmins();
    }

    public function getNextAdminId(): array
    {
        if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
            throw new ApiException('Forbidden', 403);
        }
        $next_id = $this->service->getNextAdminId();
        return ['success' => true, 'next_id' => $next_id];
    }

    public function profile(): array
    {
        $admin_id = $_SESSION['admin_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return $this->service->getProfileData($admin_id);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = clean_input($_POST['action'] ?? 'update_profile');

            if ($action === 'change_password') {
                $result = $this->service->changePassword($admin_id, $_POST);
            } elseif ($action === 'upload_avatar') {
                $file = $_FILES['avatar'] ?? null;
                $result = $this->service->uploadAvatar($admin_id, $file, true);
            } else {
                $result = $this->service->updateProfile($admin_id, $_POST);
            }

            unset($result['code']);
            return $result;
        }

        throw new ApiException('Method not allowed', 405);
    }

    public function updateAdminProfile(): array
    {
        $is_json = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
        if ($is_json) {
            $json_input = json_decode(file_get_contents('php://input'), true);
            if ($json_input !== null) {
                $_POST = array_merge($_POST, $json_input);
            }
        }

        if (isset($_POST['two_fa_enabled']) && count($_POST) <= 2) {
            $enable = intval($_POST['two_fa_enabled']);
            $result = $this->service->updateTwoFaSettings($_SESSION['admin_id'], $enable);
            unset($result['code']);
            return $result;
        }

        $this->service->validateCsrf($_POST['csrf_token'] ?? '');

        $result = $this->service->updateAdminProfile($_SESSION['admin_id'], $_POST);
        unset($result['code']);
        return $result;
    }

    public function uploadAdminPhoto(): array
    {
        $file = $_FILES['profile_photo'] ?? null;
        $result = $this->service->uploadAvatar($_SESSION['admin_id'], $file, false);
        unset($result['code']);
        return $result;
    }

    public function getAdminProfile(): array
    {
        return $this->service->getAdminProfileForUser($_SESSION['admin_id']);
    }
}
