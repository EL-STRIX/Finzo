<?php

namespace App\Application\Services;

use App\Shared\Security\SecurityService;

use App\Infrastructure\Repositories\AdminManagementRepository;
use App\Shared\Core\Exceptions\ApiException;
use PDO;

class AdminManagementService
{
    protected SecurityService $security;

    private AdminManagementRepository $repository;

    public function __construct(AdminManagementRepository $repository, ?SecurityService $security = null)
    {
        $this->security = $security ?? new \App\Shared\Security\SecurityService();
        $this->repository = $repository;
    }

    public function validateCsrf(string $token): void
    {
        if (!$this->security->validateCsrfToken($token)) {
            throw new ApiException('Invalid or expired session token', 403);
        }
    }

    public function requireSuperAdmin(): void
    {
        if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
            throw new ApiException('Forbidden: Only Super Administrator can perform this action.', 403);
        }
    }

    public function getNextAdminId(): string
    {
        $max_id = $this->repository->getMaxAdminIdNumber();
        return 'ADM' . str_pad((string)($max_id + 1), 3, '0', STR_PAD_LEFT);
    }

    public function addAdmin(array $data, ?array $file): array
    {
        $full_name = $this->security->cleanInput($data['full_name'] ?? '');
        $date_of_birth = $this->security->cleanInput($data['date_of_birth'] ?? '');
        $gender = $this->security->cleanInput($data['gender'] ?? '');
        $email = $this->security->cleanInput($data['email'] ?? '');
        $phone = $this->security->cleanInput($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $confirm_password = $data['confirm_password'] ?? '';
        $admin_role = $this->security->cleanInput($data['admin_role'] ?? '');
        $security_question = $this->security->cleanInput($data['security_question'] ?? '');
        $security_answer = $data['security_answer'] ?? '';

        if (strlen($full_name) < 2 || strlen($full_name) > 100) {
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

        if ($password !== $confirm_password) {
            throw new ApiException('Passwords do not match', 422, ['field' => 'confirm_password']);
        }

        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
            throw new ApiException('Password must be min 8 chars with uppercase, number, and special character', 422, ['field' => 'password']);
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
            'full_name' => $full_name,
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

        $this->security->writeAuditLog(
            \App\Shared\Core\Database::getInstance(), 'admin_created', $next_admin_id, 'admin', $_SESSION['admin_id'], 'admin', "New admin created: {$full_name} | Role: {$admin_role}"
        );

        return [
            'success' => true,
            'message' => 'Admin added successfully',
            'admin_id' => $next_admin_id,
            'admin_name' => $full_name,
            'code' => 201
        ];
    }

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
                $new_status = ($target['status'] === 'active') ? 'suspended' : 'active';
                $this->repository->updateStatus($target_numeric_id, $new_status);
                $this->security->writeAuditLog(\App\Shared\Core\Database::getInstance(), 'admin_status_change', $target_admin_id, 'admin', $_SESSION['admin_id'], 'admin', "Changed status of {$target['full_name']} to {$new_status}");

                return [
                    'success' => true,
                    'message' => "Admin account has been " . ($new_status === 'active' ? 'activated' : 'suspended') . " successfully.",
                    'new_status' => $new_status
                ];

            case 'delete':
                $this->security->writeAuditLog(\App\Shared\Core\Database::getInstance(), 'admin_deleted', $target_admin_id, 'admin', $_SESSION['admin_id'], 'admin', "Permanently deleted admin account: {$target['full_name']}");
                $this->repository->deleteAdmin($target_numeric_id);
                return ['success' => true, 'message' => "Admin account has been permanently removed from the system."];

            case 'reset_password':
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
                $generated_password = substr(str_shuffle($chars), 0, 10);
                $hash = password_hash($generated_password, PASSWORD_DEFAULT);

                $this->repository->resetPassword($target_numeric_id, $hash);
                $this->security->writeAuditLog(\App\Shared\Core\Database::getInstance(), 'admin_password_reset', $target_admin_id, 'admin', $_SESSION['admin_id'], 'admin', "Forced auto-generated password reset for {$target['full_name']}");

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

    public function getAdminProfileForUser(int $admin_id): array
    {
        $profile = $this->repository->getAdminProfile($admin_id);
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

    public function getAdmins(): array
    {
        $admins = $this->repository->getAllAdmins();
        return [
            'success' => true,
            'admins' => $admins,
            'total' => count($admins)
        ];
    }

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

    public function getProfileData(int $admin_id): array
    {
        $admin = $this->repository->getAdminById($admin_id);
        if (!$admin) {
            throw new ApiException('Admin not found', 404);
        }
        $recent_activity = $this->repository->getAdminActivity($admin['admin_id'], 10);
        $total_actions = $this->repository->getActivityCount($admin['admin_id']);

        return [
            'success' => true,
            'admin'   => [
                'id' => $admin['id'], 'admin_id' => $admin['admin_id'], 'full_name' => $admin['full_name'],
                'email' => $admin['email'], 'phone' => $admin['phone'], 'profile_photo' => $admin['profile_photo'],
                'status' => $admin['status'], 'last_login_at' => $admin['last_login_at'], 'last_login_ip' => $admin['last_login_ip'], 'created_at' => $admin['created_at']
            ],
            'recent_activity' => $recent_activity,
            'security' => [
                'total_actions' => $total_actions,
                'last_login_at' => $admin['last_login_at'],
                'last_login_ip' => $admin['last_login_ip']
            ]
        ];
    }

    public function changePassword(int $admin_id, array $data): array
    {
        $current = $data['current_password'] ?? '';
        $new_pwd = $data['new_password'] ?? '';
        $confirm = $data['confirm_password'] ?? '';

        if (empty($current) || empty($new_pwd)) {
            throw new ApiException('All password fields are required', 422);
        }
        if ($new_pwd !== $confirm) {
            throw new ApiException('Passwords do not match', 422);
        }
        $strength = validate_password_strength($new_pwd);
        if (!$strength['valid']) {
            throw new ApiException($strength['message'], 422);
        }

        $admin = $this->repository->getAdminById($admin_id);
        if (!password_verify($current, $admin['password_hash'])) {
            throw new ApiException('Current password is incorrect', 401);
        }

        $new_hash = password_hash($new_pwd, PASSWORD_BCRYPT);
        $this->repository->updateAdminPassword($admin_id, $new_hash);

        $this->security->writeAuditLog(\App\Shared\Core\Database::getInstance(), 'admin_password_changed', $admin_id, 'admin', $admin_id, 'admin', 'Admin password changed');

        return ['success' => true, 'message' => 'Password changed successfully'];
    }

    public function uploadAvatar(int $admin_id, ?array $file, bool $isProfileEndpoint = false): array
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

        $filename = 'admin_' . $admin_id . '_' . time() . '.' . $ext;

        // upload-admin-photo uses admin-photos, profile uses avatars
        $dir = $isProfileEndpoint ? '/uploads/avatars/' : '/uploads/admin-photos/';
        $upload_dir = dirname(__DIR__, 2) . $dir;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $admin = $this->repository->getAdminById($admin_id);
            $old_avatar = $admin['profile_photo'];

            if ($old_avatar && file_exists(dirname(__DIR__, 2) . '/' . ltrim($old_avatar, '/'))) {
                @unlink(dirname(__DIR__, 2) . '/' . ltrim($old_avatar, '/'));
            }

            $relative_path = ($isProfileEndpoint ? 'uploads/avatars/' : '/uploads/admin-photos/') . $filename;
            $this->repository->updateAdminProfileField($admin_id, 'profile_photo', $relative_path);

            $_SESSION['admin_profile_photo'] = $relative_path;

            if ($isProfileEndpoint) {
                return ['success' => true, 'message' => 'Profile photo updated successfully', 'avatar_url' => $relative_path];
            } else {
                return ['success' => true, 'photo_url' => $relative_path];
            }
        }
        throw new ApiException('Failed to move uploaded file', 500);
    }

    public function updateProfile(int $admin_id, array $data): array
    {
        $full_name = $this->security->cleanInput($data['full_name'] ?? $data['name'] ?? '');
        $email     = $this->security->cleanInput($data['email'] ?? '');
        $phone     = $this->security->cleanInput($data['phone'] ?? '');
        $password  = $data['password'] ?? '';

        if (empty($full_name)) {
            throw new ApiException('Full name is required', 422);
        }
        if (empty($password)) {
            throw new ApiException('Admin password is required for verification', 422);
        }

        $admin = $this->repository->getAdminById($admin_id);
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            throw new ApiException('Verification failed: Incorrect password', 401);
        }

        $this->repository->updateProfileFields($admin_id, ["full_name = ?", "email = ?", "phone = ?", "updated_at = NOW()"], [$full_name, $email, $phone]);

        $_SESSION['admin_name']  = $full_name;
        $_SESSION['admin_email'] = $email;

        return ['success' => true, 'message' => 'Profile updated successfully'];
    }

    public function updateTwoFaSettings(int $admin_id, int $enabled): array
    {
        $this->repository->updateTwoFa($admin_id, $enabled);
        return ['success' => true, 'message' => '2FA settings updated'];
    }

    public function updateAdminProfile(int $admin_id, array $data): array
    {
        if (!empty($data['new_password'])) {
            $current_pwd = $data['current_password'] ?? '';
            $new_pwd = $data['new_password'];
            $confirm_pwd = $data['confirm_new_password'] ?? '';

            if ($new_pwd !== $confirm_pwd) {
                throw new ApiException('Passwords do not match', 422, ['field' => 'confirm_new_password']);
            }

            $admin = $this->repository->getAdminById($admin_id);
            if (!password_verify($current_pwd, $admin['password_hash'])) {
                throw new ApiException('Incorrect current password', 422, ['field' => 'current_password']);
            }
            if ($new_pwd === $current_pwd) {
                throw new ApiException('New password cannot be the same as current', 422, ['field' => 'new_password']);
            }
            if (strlen($new_pwd) < 8 || !preg_match('/[A-Z]/', $new_pwd) || !preg_match('/[0-9]/', $new_pwd) || !preg_match('/[^a-zA-Z0-9]/', $new_pwd)) {
                throw new ApiException('Password must be min 8 chars with uppercase, number, and special character', 422, ['field' => 'new_password']);
            }

            $new_hash = password_hash($new_pwd, PASSWORD_BCRYPT);
            $this->repository->updateAdminPassword($admin_id, $new_hash);
            $this->security->writeAuditLog(\App\Shared\Core\Database::getInstance(), 'admin_password_updated', $admin_id, 'admin', $admin_id, 'admin', 'Password updated successfully');

            return ['success' => true, 'message' => 'Password updated successfully'];
        }

        $full_name = $this->security->cleanInput($data['full_name'] ?? '');
        $phone = $this->security->cleanInput($data['phone'] ?? '');
        $date_of_birth = $this->security->cleanInput($data['date_of_birth'] ?? '');
        $gender = $this->security->cleanInput($data['gender'] ?? '');
        $security_question = isset($data['security_question']) ? $this->security->cleanInput($data['security_question']) : null;
        $security_answer = $data['security_answer'] ?? '';

        if (strlen($full_name) < 2 || strlen($full_name) > 100) {
            throw new ApiException('Full name must be between 2 and 100 characters', 422, ['field' => 'full_name']);
        }
        if (!empty($date_of_birth) && strtotime($date_of_birth) > strtotime('-18 years')) {
            throw new ApiException('Must be at least 18 years old', 422, ['field' => 'date_of_birth']);
        }
        if (!empty($phone) && !validate_phone($phone)) {
            throw new ApiException('Invalid phone number format', 422, ['field' => 'phone']);
        }

        $fields = ["full_name = ?", "phone = ?", "date_of_birth = ?", "gender = ?"];
        $params = [$full_name, $phone, $date_of_birth, $gender];

        if ($security_question !== null && $security_question !== '') {
            $fields[] = "security_question = ?";
            $params[] = $security_question;
        }

        if (!empty($security_answer)) {
            $fields[] = "security_answer_hash = ?";
            $params[] = password_hash(strtolower(trim($security_answer)), PASSWORD_BCRYPT);
        }

        $this->repository->updateProfileFields($admin_id, $fields, $params);
        $this->security->writeAuditLog(\App\Shared\Core\Database::getInstance(), 'admin_profile_updated', $admin_id, 'admin', $admin_id, 'admin', 'Profile information updated');

        return ['success' => true, 'message' => 'Profile updated successfully'];
    }
}
