<?php

namespace App\Presentation\Api\V1\Admin;

use App\Shared\Security\SecurityService;

use App\Application\Services\AdminManagementService;
use App\Shared\Core\Exceptions\ApiException;
use Exception;

class AdminManagementController
{
    protected SecurityService $security;

    private AdminManagementService $service;

    public function __construct(\App\Domain\Contracts\Services\AdminManagementServiceInterface $service, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security;
        $this->service = $service;
    }

    public function addAdmin(?string $id = null, ?string $extra = null): array
    {
        $this->security->enforcePermission('manage_admins');

        $file = (\App\Shared\Core\Validation\Validator::isSet($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) ? $_FILES['profile_photo'] : null;
        $result = $this->service->addAdmin($_POST, $file);

        unset($result['code']);
        return $result;
    }


    public function adminAction(?string $id = null, ?string $extra = null): array
    {

        $this->security->enforcePermission('manage_admins');

        $action = $this->security->cleanInput($_POST['action'] ?? '');
        $target_id = \App\Shared\Core\Validation\Validator::isSet(($id ?? $_POST['admin_id'] ?? null)) ? (int)($id ?? $_POST['admin_id'] ?? null) : 0;

        $result = $this->service->handleAdminAction($action, $target_id);
        unset($result['code']);
        return $result;
    }

    public function getAdminDetail(?string $id = null, ?string $extra = null): array
    {
        $this->security->enforcePermission('manage_admins');

        $target_id = \App\Shared\Core\Validation\Validator::isSet(($id ?? $_GET['id'] ?? null)) ? (int)($id ?? $_GET['id'] ?? null) : 0;
        return $this->service->getAdminDetail($target_id);
    }

    public function getAdmins(?string $id = null, ?string $extra = null): array
    {
        $this->security->enforcePermission('manage_admins');
        return $this->service->getAdmins();
    }

    public function getLockedAdmins(?string $id = null, ?string $extra = null): array
    {
        $this->security->enforcePermission('manage_admins');
        return $this->service->getLockedAdmins();
    }

    public function getNextAdminId(?string $id = null, ?string $extra = null): array
    {
        $this->security->enforcePermission('manage_admins');
        $next_id = $this->service->getNextAdminId();
        return ['success' => true, 'next_id' => $next_id];
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
            $result = $this->service->updateTwoFaSettings(\App\Shared\Security\Auth::user()['id'], $enable);
            unset($result['code']);
            return $result;
        }



        $result = $this->service->updateAdminProfile(\App\Shared\Security\Auth::user()['id'], $_POST);
        unset($result['code']);
        return $result;
    }

    public function uploadAdminPhoto(?string $id = null, ?string $extra = null): array
    {
        $file = $_FILES['profile_photo'] ?? null;
        $result = $this->service->uploadAvatar(\App\Shared\Security\Auth::user()['id'], $file, false);
        unset($result['code']);
        return $result;
    }

    public function getAdminProfile(?string $id = null, ?string $extra = null): array
    {
        return $this->service->getAdminProfileForUser(\App\Shared\Security\Auth::user()['id']);
    }

    public function updateSecurityQuestion(?string $id = null, ?string $extra = null): array
    {
        $is_json = (\App\Shared\Core\Validation\Validator::isSet($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
        if ($is_json) {
            $json_input = json_decode(file_get_contents('php://input'), true);
            if ($json_input !== null) {
                $_POST = array_merge($_POST, $json_input);
            }
        }

        $result = $this->service->updateSecurityQuestion(\App\Shared\Security\Auth::user()['id'], $_POST);
        unset($result['code']);
        return $result;
    }

    public function unlockAdmin(?string $id = null, ?string $extra = null): array
    {
        $is_json = (\App\Shared\Core\Validation\Validator::isSet($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
        if ($is_json) {
            $json_input = json_decode(file_get_contents('php://input'), true);
            if ($json_input !== null) {
                $_POST = array_merge($_POST, $json_input);
            }
        }

        $target_id = (int)($id ?? $_POST['target_admin_id'] ?? $_POST['admin_id'] ?? 0);
        $result = $this->service->unlockAdmin($target_id);
        unset($result['code']);
        return $result;
    }
}


