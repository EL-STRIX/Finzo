<?php

namespace App\Presentation\Controllers\Admin;

use App\Shared\Security\SecurityService;

use App\Application\Services\AdminManagementService;
use App\Shared\Core\Exceptions\ApiException;
use Exception;

class AdminManagementController
{
    protected SecurityService $security;

    private AdminManagementService $service;

    public function __construct(AdminManagementService $service, ?SecurityService $security = null)
    {
        $this->security = $security ?? new \App\Shared\Security\SecurityService();
        $this->service = $service;
    }

    public function addAdmin(?string $id = null, ?string $extra = null): array
    {
        $this->service->validateCsrf($_POST['csrf_token'] ?? '');
        $this->service->requireSuperAdmin();

        $file = (\App\Shared\Core\Validation\Validator::isSet($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) ? $_FILES['profile_photo'] : null;
        $result = $this->service->addAdmin($_POST, $file);

        $code = $result['code'] ?? 200;
        unset($result['code']);

        if ($code === 201) {
            json_response($result, 201);
        }

        return $result;
    }

    public function adminAction(?string $id = null, ?string $extra = null): array
    {
        $this->service->validateCsrf($_POST['csrf_token'] ?? '');
        $this->service->requireSuperAdmin();

        $action = $this->security->cleanInput($_POST['action'] ?? '');
        $target_id = \App\Shared\Core\Validation\Validator::isSet(($id ?? $_POST['admin_id'] ?? null)) ? (int)($id ?? $_POST['admin_id'] ?? null) : 0;

        $result = $this->service->handleAdminAction($action, $target_id);
        unset($result['code']);
        return $result;
    }

    public function getAdminDetail(?string $id = null, ?string $extra = null): array
    {
        $this->service->requireSuperAdmin();

        $target_id = \App\Shared\Core\Validation\Validator::isSet(($id ?? $_GET['id'] ?? null)) ? (int)($id ?? $_GET['id'] ?? null) : 0;
        return $this->service->getAdminDetail($target_id);
    }

    public function getAdmins(?string $id = null, ?string $extra = null): array
    {
        $this->service->requireSuperAdmin();
        return $this->service->getAdmins();
    }

    public function getLockedAdmins(?string $id = null, ?string $extra = null): array
    {
        if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
            throw new ApiException('Unauthorized. Super Admin access required.', 403);
        }
        return $this->service->getLockedAdmins();
    }

    public function getNextAdminId(?string $id = null, ?string $extra = null): array
    {
        if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
            throw new ApiException('Forbidden', 403);
        }
        $next_id = $this->service->getNextAdminId();
        return ['success' => true, 'next_id' => $next_id];
    }

    public function profile(?string $id = null, ?string $extra = null): array
    {
        $adminId = $_SESSION['admin_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return $this->service->getProfileData($adminId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->security->cleanInput($_POST['action'] ?? 'update_profile');

            if ($action === 'change_password') {
                $result = $this->service->changePassword($adminId, $_POST);
            } elseif ($action === 'upload_avatar') {
                $file = $_FILES['avatar'] ?? null;
                $result = $this->service->uploadAvatar($adminId, $file, true);
            } else {
                $result = $this->service->updateProfile($adminId, $_POST);
            }

            unset($result['code']);
            return $result;
        }

        throw new ApiException('Method not allowed', 405);
    }

    public function updateAdminProfile(?string $id = null, ?string $extra = null): array
    {
        $is_json = (\App\Shared\Core\Validation\Validator::isSet($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
        if ($is_json) {
            $json_input = json_decode(file_get_contents('php://input'), true);
            if ($json_input !== null) {
                $_POST = array_merge($_POST, $json_input);
            }
        }

        if (\App\Shared\Core\Validation\Validator::isSet($_POST['two_fa_enabled']) && count($_POST) <= 2) {
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

    public function uploadAdminPhoto(?string $id = null, ?string $extra = null): array
    {
        $file = $_FILES['profile_photo'] ?? null;
        $result = $this->service->uploadAvatar($_SESSION['admin_id'], $file, false);
        unset($result['code']);
        return $result;
    }

    public function getAdminProfile(?string $id = null, ?string $extra = null): array
    {
        return $this->service->getAdminProfileForUser($_SESSION['admin_id']);
    }
}
