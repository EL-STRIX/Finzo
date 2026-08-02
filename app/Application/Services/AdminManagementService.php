<?php

namespace App\Application\Services;

use App\Shared\Security\SecurityService;

use App\Infrastructure\Repositories\AdminManagementRepository;
use App\Shared\Core\Exceptions\ApiException;
use PDO;

class AdminManagementService implements \App\Domain\Contracts\Services\AdminManagementServiceInterface
{
    protected SecurityService $security;

    private AdminManagementRepository $repository;
    /**
     *   construct.
     *
     * @param \App\Domain\Contracts\Repositories\AdminManagementRepositoryInterface $repository
     * @param ?\App\Domain\Contracts\Security\SecurityServiceInterface $security
     */
    public function __construct(\App\Domain\Contracts\Repositories\AdminManagementRepositoryInterface $repository, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security;
        $this->repository = $repository;
    }


    /**
     * Require Super Admin.
     *
     * @return void
     */
    public function requireSuperAdmin(): void
    {
        if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
            throw new ApiException('Forbidden: Only Super Administrator can perform this action.', 403);
        }
    }
    /**
     * Get Next Admin Id.
     *
     * @return string
     */
    public function getNextAdminId(): string
    {
        $max_id = $this->repository->getMaxAdminIdNumber();
        return 'ADM' . str_pad((string)($max_id + 1), 3, '0', STR_PAD_LEFT);
    }
    /**
     * Add Admin.
     *
     * @param array $data
     * @param ?array $file
     * @return array
     */
    public function addAdmin(array $data, ?array $file): array
    {
        $fullName = $this->security->cleanInput($data['full_name'] ?? '');
        $date_of_birth = $this->security->cleanInput($data['date_of_birth'] ?? '');
        $gender = $this->security->cleanInput($data['gender'] ?? '');
        $email = $this->security->cleanInput($data['email'] ?? '');
        $phone = $this->security->cleanInput($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $admin_role = $this->security->cleanInput($data['admin_role'] ?? '');
        $security_question = $this->security->cleanInput($data['security_question'] ?? '');
        $security_answer = $data['security_answer'] ?? '';

        if (strlen($fullName) < 2 || strlen($fullName) > 100) {
            throw new ApiException('Full name must be between 2 and 100 characters', 422, ['field' => 'full_name']);
        }
        if (empty($date_of_birth) || strtotime($date_of_birth) > strtotime('-18 years')) {
            throw new ApiException('Must be at least 18 years old', 422, ['field' => 'date_of_birth']);
        }
        if (!in_array($gender, ['Male', 'Female', 'Other', 'Prefer not to say'])) {
            throw new ApiException('Invalid gender selection', 422, ['field' => 'gender']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ApiException('Invalid email format', 422, ['field' => 'email']);
        }
        if (!validate_phone($phone)) {
            throw new ApiException('Invalid phone number (must be 10 digits)', 422, ['field' => 'phone']);
        }

        if ($this->repository->getAdminByEmail($email)) {
            throw new ApiException('An admin with this email already exists', 409, ['field' => 'email']);
        }

        if ($password !== $confirmPassword) {
            throw new ApiException('Passwords do not match', 422, ['field' => 'confirm_password']);
        }

        $pw_check = \validate_password_strength($password);
        if (!$pw_check['valid']) {
            throw new ApiException($pw_check['message'], 422, ['field' => 'password']);
        }

        if (!in_array($admin_role, ['admin', 'moderator'])) {
            throw new ApiException('Invalid role assigned', 422, ['field' => 'admin_role']);
        }

        if (empty($security_question) || empty($security_answer)) {
            throw new ApiException('Security question and answer are required', 422);
        }

        $photo_path = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png']) || !in_array($file['type'], ['image/jpeg', 'image/png'])) {
                throw new ApiException('Profile photo must be JPG or PNG', 422);
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new ApiException('Profile photo max size is 5MB', 422);
            }

            $upload_dir = UPLOAD_ADMIN_PHOTOS_PATH;
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $filename = 'admin_' . uniqid() . '.' . $ext;
            $dest = $upload_dir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                throw new ApiException('Failed to save profile photo', 500);
            }
            $photo_path = '/uploads/admin-photos/' . $filename;
        }

        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $security_answer_lower = strtolower(trim($security_answer));
        $answer_hash = password_hash($security_answer_lower, PASSWORD_BCRYPT);
        $access_level = ($admin_role === 'admin') ? 'full' : 'restricted';

        $next_admin_id = $this->getNextAdminId();
        $creator = $this->repository->getAdminById($_SESSION['admin_id']);
        $creator_admin_id = $creator['admin_id'] ?? null;

        $insertData = [
            'admin_id' => $next_admin_id,
            'full_name' => $fullName,
            'date_of_birth' => $date_of_birth,
            'gender' => $gender,
            'profile_photo' => $photo_path,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => $password_hash,
            'security_question' => $security_question,
            'security_answer_hash' => $answer_hash,
            'admin_role' => $admin_role,
            'access_level' => $access_level,
            'created_by' => $creator_admin_id
        ];

        $this->repository->createAdmin($insertData);

        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('admin_created', $next_admin_id, \App\Domain\Enums\RoleEnum::ADMIN->value, $_SESSION['admin_id'], \App\Domain\Enums\RoleEnum::ADMIN->value, "New admin created: {$fullName} | Role: {$admin_role}")
);

        return [
            'success' => true,
            'message' => 'Admin added successfully',
            'admin_id' => $next_admin_id,
            'admin_name' => $fullName,
            'code' => 201
        ];
    }
    /**
     * Handle Admin Action.
     *
     * @param string $action
     * @param int $target_numeric_id
     * @return array
     */
    public function handleAdminAction(string $action, int $target_numeric_id): array
    {
        if ($target_numeric_id <= 0) {
            throw new ApiException('Invalid Admin ID', 400);
        }
        if ($target_numeric_id === (int)$_SESSION['admin_id']) {
            throw new ApiException('Self-management not permitted via this interface.', 400);
        }

        $target = $this->repository->getAdminById($target_numeric_id);
        if (!$target) {
            throw new ApiException('Admin not found.', 404);
        }
        if ($target['admin_role'] === 'super_admin') {
            throw new ApiException('Cannot modify a Super Administrator.', 403);
        }

        $target_admin_id = $target['admin_id'];

        switch ($action) {
            case 'toggle_status':
                $new_status = ($target['status'] === \App\Domain\Enums\AccountStatusEnum::ACTIVE->value) ? 'suspended' : 'active';
                $this->repository->updateStatus($target_numeric_id, $new_status);
                \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('admin_status_change', $target_admin_id, \App\Domain\Enums\RoleEnum::ADMIN->value, $_SESSION['admin_id'], \App\Domain\Enums\RoleEnum::ADMIN->value, "Changed status of {$target['full_name']} to {$new_status}")
);

                return [
                    'success' => true,
                    'message' => "Admin account has been " . ($new_status === \App\Domain\Enums\AccountStatusEnum::ACTIVE->value ? 'activated' : 'suspended') . " successfully.",
                    'new_status' => $new_status
                ];

            case 'delete':
                \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('admin_deleted', $target_admin_id, \App\Domain\Enums\RoleEnum::ADMIN->value, $_SESSION['admin_id'], \App\Domain\Enums\RoleEnum::ADMIN->value, "Permanently deleted admin account: {$target['full_name']}")
);
                $this->repository->deleteAdmin($target_numeric_id);
                return ['success' => true, 'message' => "Admin account has been permanently removed from the system."];

            case 'reset_password':
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
                $generated_password = substr(str_shuffle($chars), 0, 10);
                $hash = password_hash($generated_password, PASSWORD_DEFAULT);

                $this->repository->resetPassword($target_numeric_id, $hash);
                \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('admin_password_reset', $target_admin_id, \App\Domain\Enums\RoleEnum::ADMIN->value, $_SESSION['admin_id'], \App\Domain\Enums\RoleEnum::ADMIN->value, "Forced auto-generated password reset for {$target['full_name']}")
);

                return [
                    'success' => true,
                    'message' => "A new temporary password has been generated for {$target['full_name']}.",
                    'generated_password' => $generated_password,
                    'expiry' => date('Y-m-d H:i:s', strtotime('+24 hours'))
                ];

            default:
                throw new ApiException('Invalid action', 400);
        }
    }
    /**
     * Get Admin Detail.
     *
     * @param int $target_id
     * @return array
     */
    public function getAdminDetail(int $target_id): array
    {
        if ($target_id <= 0) {
            throw new ApiException('Invalid Admin ID', 400);
        }
        if ($target_id === (int)$_SESSION['admin_id']) {
            throw new ApiException('Please use your profile page for self-management.', 400);
        }

        $admin = $this->repository->getAdminDetail($target_id);
        if (!$admin) {
            throw new ApiException('Admin not found or restricted.', 404);
        }

        $activity = $this->repository->getAdminActivity($admin['admin_id'], 50);

        return [
            'success' => true,
            'admin' => $admin,
            'activity' => $activity
        ];
    }
    /**
     * Get Admin Profile For User.
     *
     * @param int $adminId
     * @return array
     */
    public function getAdminProfileForUser(int $adminId): array
    {
        $profile = $this->repository->getAdminProfile($adminId);
        if (!$profile) {
            throw new ApiException('Profile not found', 404);
        }
        $activity = $this->repository->getAdminActivity($profile['admin_id'], 100);
        return [
            'success' => true,
            'profile' => $profile,
            'activity' => $activity
        ];
    }
    /**
     * Get Admins.
     *
     * @return array
     */
    public function getAdmins(): array
    {
        $admins = $this->repository->getAllAdmins();
        return [
            'success' => true,
            'admins' => $admins,
            'total' => count($admins)
        ];
    }
    /**
     * Get Locked Admins.
     *
     * @return array
     */
    public function getLockedAdmins(): array
    {
        $locked = $this->repository->getLockedAdmins();
        foreach ($locked as &$admin) {
            $admin['security_question_set'] = (bool)$admin['security_question_set'];
        }
        return [
            'success' => true,
            'locked_admins' => $locked,
            'total' => count($locked)
        ];
    }




    /**
     * Upload Avatar.
     *
     * @param int $adminId
     * @param ?array $file
     * @param bool $isProfileEndpoint
     * @return array
     */
    public function uploadAvatar(int $adminId, ?array $file, bool $isProfileEndpoint = false): array
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new ApiException($isProfileEndpoint ? 'No file uploaded or upload error' : 'No valid file uploaded', 422);
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);

        if (!in_array($mime, $allowed_types)) {
            throw new ApiException($isProfileEndpoint ? 'Invalid file type. Only JPG, PNG, and WEBP are allowed.' : 'Profile photo must be JPG or PNG', 422);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!$isProfileEndpoint) {
            if (!in_array($ext, ['jpg', 'jpeg', 'png']) || !in_array($file['type'], ['image/jpeg', 'image/png'])) {
                throw new ApiException('Profile photo must be JPG or PNG', 422);
            }
            if ($file['size'] > 10 * 1024 * 1024) {
                throw new ApiException('Profile photo max size is 10MB', 422);
            }
        }

        $filename = 'admin_' . $adminId . '_' . time() . '.' . $ext;

        // upload-admin-photo uses admin-photos, profile uses avatars
        $dir = $isProfileEndpoint ? '/uploads/avatars/' : '/uploads/admin-photos/';
        $upload_dir = dirname(__DIR__, 2) . $dir;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $admin = $this->repository->getAdminById($adminId);
            $old_avatar = $admin['profile_photo'];

            if ($old_avatar && file_exists(dirname(__DIR__, 2) . '/' . ltrim($old_avatar, '/'))) {
                @unlink(dirname(__DIR__, 2) . '/' . ltrim($old_avatar, '/'));
            }

            $relative_path = ($isProfileEndpoint ? 'uploads/avatars/' : '/uploads/admin-photos/') . $filename;
            $this->repository->updateAdminProfileField($adminId, 'profile_photo', $relative_path);

            $_SESSION['admin_profile_photo'] = $relative_path;

            if ($isProfileEndpoint) {
                return ['success' => true, 'message' => 'Profile photo updated successfully', 'avatar_url' => $relative_path];
            } else {
                return ['success' => true, 'photo_url' => $relative_path];
            }
        }
        throw new ApiException('Failed to move uploaded file', 500);
    }


    /**
     * Update Two Fa Settings.
     *
     * @param int $adminId
     * @param int $enabled
     * @return array
     */
    public function updateTwoFaSettings(int $adminId, int $enabled): array
    {
        $this->repository->updateTwoFa($adminId, $enabled);
        return ['success' => true, 'message' => '2FA settings updated'];
    }
    /**
     * Update Admin Profile.
     *
     * @param int $adminId
     * @param array $data
     * @return array
     */
    public function updateAdminProfile(int $adminId, array $data): array
    {
        if (!empty($data['new_password'])) {
            $current_pwd = $data['current_password'] ?? '';
            $new_pwd = $data['new_password'];
            $confirm_pwd = $data['confirm_new_password'] ?? '';

            if ($new_pwd !== $confirm_pwd) {
                throw new ApiException('Passwords do not match', 422, ['field' => 'confirm_new_password']);
            }

            $admin = $this->repository->getAdminById($adminId);
            if (!password_verify($current_pwd, $admin['password_hash'])) {
                throw new ApiException('Incorrect current password', 422, ['field' => 'current_password']);
            }
            if ($new_pwd === $current_pwd) {
                throw new ApiException('New password cannot be the same as current', 422, ['field' => 'new_password']);
            }
            $pw_check = \validate_password_strength($new_pwd);
            if (!$pw_check['valid']) {
                throw new ApiException($pw_check['message'], 422, ['field' => 'new_password']);
            }

            $new_hash = password_hash($new_pwd, PASSWORD_BCRYPT);
            $this->repository->updateAdminPassword($adminId, $new_hash);
            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('admin_password_updated', $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, 'Password updated successfully')
);

            return ['success' => true, 'message' => 'Password updated successfully'];
        }

        $fullName = $this->security->cleanInput($data['full_name'] ?? '');
        $phone = $this->security->cleanInput($data['phone'] ?? '');
        $date_of_birth = $this->security->cleanInput($data['date_of_birth'] ?? '');
        $gender = $this->security->cleanInput($data['gender'] ?? '');
        $security_question = isset($data['security_question']) ? $this->security->cleanInput($data['security_question']) : null;
        $security_answer = $data['security_answer'] ?? '';

        if (strlen($fullName) < 2 || strlen($fullName) > 100) {
            throw new ApiException('Full name must be between 2 and 100 characters', 422, ['field' => 'full_name']);
        }
        if (!empty($date_of_birth) && strtotime($date_of_birth) > strtotime('-18 years')) {
            throw new ApiException('Must be at least 18 years old', 422, ['field' => 'date_of_birth']);
        }
        if (!empty($phone) && !validate_phone($phone)) {
            throw new ApiException('Invalid phone number format', 422, ['field' => 'phone']);
        }

        $fields = ["full_name = ?", "phone = ?", "date_of_birth = ?", "gender = ?"];
        $params = [$fullName, $phone, $date_of_birth, $gender];

        if ($security_question !== null && $security_question !== '') {
            $fields[] = "security_question = ?";
            $params[] = $security_question;
        }

        if (!empty($security_answer)) {
            $fields[] = "security_answer_hash = ?";
            $params[] = password_hash(strtolower(trim($security_answer)), PASSWORD_BCRYPT);
        }

        $this->repository->updateProfileFields($adminId, $fields, $params);
        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('admin_profile_updated', $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, 'Profile information updated')
);

        return ['success' => true, 'message' => 'Profile updated successfully'];
    }
}
