<?php

namespace App\Application\Services;

use App\Shared\Security\SecurityService;

use App\Infrastructure\Repositories\AuthRepository;
use App\Shared\Core\Exceptions\ApiException;
use PDOException;

class AuthService
{
    protected SecurityService $security;

    private AuthRepository $repository;

    public function __construct(AuthRepository $repository, ?SecurityService $security = null)
    {
        $this->security = $security ?? new \App\Shared\Security\SecurityService();
        $this->repository = $repository;
    }

    public function loginUser(string $identity, string $password): array
    {
        $user = $this->repository->findUserByIdentity($identity);

        if (!$user) {
            throw new ApiException('Invalid email or password', 401);
        }

        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
            throw new ApiException("Account locked due to too many failed attempts. Try again in {$remaining} minute(s).", 423);
        }

        if ($user['status'] === 'rejected') {
            throw new ApiException('Your application has been rejected. Please contact support for assistance.', 403);
        }

        if ($user['status'] === 'suspended') {
            throw new ApiException('Your account has been suspended. Please contact support.', 403);
        }

        if (!password_verify($password, $user['password_hash'])) {
            $new_attempts = $user['failed_attempts'] + 1;

            $pdo = $this->repository->getDb();
            $max_attempts = get_setting($pdo, 'max_failed_login_attempts', 5);
            $lockout_duration = get_setting($pdo, 'customer_lockout_duration', LOCKOUT_DURATION);

            if ($new_attempts >= $max_attempts) {
                $lock_until = date('Y-m-d H:i:s', time() + $lockout_duration);
                $this->repository->lockUser($user['id'], $new_attempts, $lock_until);

                \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('account_locked', $user['id'], 'user', $user['id'], 'user', "Account locked after {$new_attempts} failed login attempts")
);

                $lockout_minutes = ceil($lockout_duration / 60);
                throw new ApiException("Account locked due to too many failed attempts. Try again in {$lockout_minutes} minutes.", 423);
            } else {
                $this->repository->updateFailedAttempts($user['id'], $new_attempts);

                $remaining = $max_attempts - $new_attempts;
                throw new ApiException("Invalid email or password. {$remaining} attempt(s) remaining.", 401);
            }
        }

        $ip = get_client_ip();
        $this->repository->updateSuccessfulLogin($user['id'], $ip);

        $account = $this->repository->fetchActiveAccount($user['customer_id']);

        $pdo = $this->repository->getDb();
        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('user_login', $user['id'], 'user', $user['id'], 'user', "Customer login from IP: {$ip}")
);

        return [
            'mfa_required' => false,
            'user'         => $user,
            'account'      => $account
        ];
    }

    public function loginAdmin(string $email, string $password): array
    {
        $admin = $this->repository->findAdminByEmail($email);

        if (!$admin) {
            throw new ApiException('Invalid email or password', 401);
        }

        if ($admin['temp_password_expiry'] && strtotime($admin['temp_password_expiry']) < time()) {
            throw new ApiException('Your temporary password has expired. Please contact a super administrator for a new one.', 401);
        }

        if ($admin['status'] === 'suspended') {
            throw new ApiException('Your account has been suspended. Contact the system administrator.', 403);
        }

        if ($admin['account_locked'] == 1) {
            if ($admin['auto_unlock_at'] && strtotime($admin['auto_unlock_at']) <= time()) {
                $this->repository->autoUnlockAdmin($admin['id']);
                $admin['account_locked'] = 0;
                $admin['failed_attempts'] = 0;
            } else {
                throw new ApiException('Account locked.', 401, [
                    'locked' => true,
                    'security_question_set' => (bool)$admin['security_question_set'],
                    'auto_unlock_at' => $admin['auto_unlock_at']
                ]);
            }
        }

        $ip = get_client_ip();

        if (!password_verify($password, $admin['password_hash'])) {
            $new_attempts = (int)$admin['failed_attempts'] + 1;

            if ($new_attempts >= 3) {
                $auto_unlock_at = $this->repository->lockAdmin($admin['id']);
                $this->repository->logAdminLock($admin['admin_id'], $ip, $admin['id']);

                $pdo = $this->repository->getDb();
                \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('admin_account_locked', $admin['admin_id'], 'admin', $admin['admin_id'], 'admin', 'Account locked: 3 wrong passwords')
);

                throw new ApiException('Account locked.', 401, [
                    'locked' => true,
                    'security_question_set' => (bool)$admin['security_question_set'],
                    'auto_unlock_at' => $auto_unlock_at
                ]);
            } else {
                $this->repository->updateAdminFailedAttempts($admin['id'], $new_attempts);

                $remaining = 3 - $new_attempts;
                throw new ApiException("Invalid password. {$remaining} attempt(s) remaining before lockout.", 401, [
                    'locked' => false
                ]);
            }
        }

        $this->repository->updateAdminSuccessfulLogin($admin['id'], $ip);

        $pdo = $this->repository->getDb();
        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('admin_login', $admin['admin_id'], 'admin', $admin['admin_id'], 'admin', "Admin login from IP: {$ip}")
);

        return [
            'mfa_required' => false,
            'admin'        => $admin
        ];
    }

    public function loginStaff(string $identity, string $password): array
    {
        $staff = $this->repository->findStaffByIdentity($identity);

        if (!$staff) {
            throw new ApiException('Invalid email/ID or password', 401);
        }

        if ($staff['locked_until'] && strtotime($staff['locked_until']) > time()) {
            $remaining = ceil((strtotime($staff['locked_until']) - time()) / 60);
            throw new ApiException("Account locked due to too many failed attempts. Try again in {$remaining} minute(s).", 423);
        }

        if ($staff['status'] === 'suspended') {
            throw new ApiException('Your account has been suspended. Please contact the administrator.', 403);
        }

        if (!password_verify($password, $staff['password_hash'])) {
            $new_attempts = $staff['failed_attempts'] + 1;

            $pdo = $this->repository->getDb();
            $max_attempts = get_setting($pdo, 'max_failed_login_attempts', 5);
            $lockout_duration = get_setting($pdo, 'staff_lockout_duration', LOCKOUT_DURATION);

            if ($new_attempts >= $max_attempts) {
                $lock_until = date('Y-m-d H:i:s', time() + $lockout_duration);
                $this->repository->lockStaff($staff['id'], $new_attempts, $lock_until);

                \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('account_locked', $staff['staff_id'], 'staff', $staff['staff_id'], 'staff', "Staff account locked after {$new_attempts} failed login attempts")
);

                $lockout_minutes = ceil($lockout_duration / 60);
                throw new ApiException("Account locked due to too many failed attempts. Try again in {$lockout_minutes} minutes.", 423);
            } else {
                $this->repository->updateStaffFailedAttempts($staff['id'], $new_attempts);

                $remaining = $max_attempts - $new_attempts;
                throw new ApiException("Invalid email/ID or password. {$remaining} attempt(s) remaining.", 401);
            }
        }

        $ip = get_client_ip();
        $this->repository->updateStaffSuccessfulLogin($staff['id'], $ip);

        $pdo = $this->repository->getDb();
        \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('staff_login', $staff['staff_id'], 'staff', $staff['staff_id'], 'staff', "Staff login from IP: {$ip}")
);

        return [
            'mfa_required' => false,
            'staff'        => $staff
        ];
    }

    public function forgotPassword(string $email): bool
    {
        $user = $this->repository->findUserByEmail($email);

        if (!$user) {
            // Silently succeed to prevent email enumeration
            return true;
        }

        try {
            $this->repository->deletePasswordResets($user['customer_id']);

            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + 3600);

            $this->repository->createPasswordReset($user['customer_id'], $token, $expires_at);

            require_once FINZO_ROOT . '/config/mailer.php';
            send_password_reset_email($user['email'], $user['full_name'], $token);

            return true;
        } catch (PDOException $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Forgot password error: ', ['context' => $e->getMessage()]);
            throw new ApiException('An error occurred. Please try again.', 500);
        }
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $reset = $this->repository->findPasswordReset($token);

        if (!$reset) {
            throw new ApiException('Invalid or expired reset link. Please request a new one.', 400);
        }

        if (strtotime($reset['expires_at']) < time()) {
            $this->repository->deletePasswordResetById($reset['id']);
            throw new ApiException('This reset link has expired. Please request a new one.', 400);
        }

        try {
            $this->repository->beginTransaction();

            $password_hash = password_hash($newPassword, PASSWORD_BCRYPT);

            $this->repository->resetUserPassword($reset['user_id'], $password_hash);
            $this->repository->deletePasswordResets($reset['user_id']);
            $this->repository->resetUserAttempts($reset['user_id']);

            $this->repository->commit();

            return true;
        } catch (PDOException $e) {
            $this->repository->rollBack();
            \App\Shared\Core\Logging\Logger::getInstance()->error('Reset password error: ', ['context' => $e->getMessage()]);
            throw new ApiException('An error occurred. Please try again.', 500);
        }
    }

    public function registerUser(string $fullName, string $email, string $phone, string $password): bool
    {
        if ($this->repository->findUserByEmailAny($email)) {
            throw new ApiException('Validation failed', 409, ['errors' => ['email' => 'This email is already registered']]);
        }

        if ($this->repository->findUserByPhone($phone)) {
            throw new ApiException('Validation failed', 409, ['errors' => ['phone' => 'This phone number is already registered']]);
        }

        try {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $this->repository->beginTransaction();

            $customer_id = $this->repository->getNextCustomerId();

            $userId = $this->repository->createUser($customer_id, $fullName, $email, $phone, $password_hash);

            if (session_status() === PHP_SESSION_ACTIVE) {
                \App\Shared\Security\SessionManager::regenerate(true);
            }
            $_SESSION['user_id']     = (int)$userId;
            $_SESSION['user_name']   = $fullName;
            $_SESSION['user_email']  = $email;
            $_SESSION['user_phone']  = $phone;
            $_SESSION['user_status'] = 'pending';
            $_SESSION['role']        = 'customer';
            $_SESSION['last_activity'] = time();

            $welcomeMsg = "Your account has been registered. Complete your profile to start banking.";
            $this->repository->createNotification($customer_id, 'system', 'Welcome to Finzo!', $welcomeMsg);

            $profileMsg = "Please complete your identity verification to unlock full banking services.";
            $this->repository->createNotification($customer_id, 'security', 'Profile Incomplete', $profileMsg);

            $this->repository->commit();

            return true;
        } catch (PDOException $e) {
            $this->repository->rollBack();
            \App\Shared\Core\Logging\Logger::getInstance()->error('Registration error: ', ['context' => $e->getMessage()]);
            throw new ApiException('Registration failed. Please try again.', 500);
        }
    }
}
