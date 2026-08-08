<?php

namespace App\Presentation\Api\V1\Auth;

use App\Shared\Security\SecurityService;

use App\Application\Services\AuthService;
use App\Shared\Core\Exceptions\ApiException;

class AuthController
{
    protected SecurityService $security;

    private AuthService $service;

    public function __construct(\App\Domain\Contracts\Services\AuthServiceInterface $service, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security;
        $this->service = $service;
    }

    /**
     * Authenticate a standard banking user.
     *
     * @param string|null $id Optional identity override
     * @param string|null $extra Optional extra parameter
     * @return array{success: bool, mfa_required?: bool, mfa_token?: string, redirect: string, message?: string, user?: array{full_name: string, email: string, status: string, account_no: string, account_type: string, balance: string}}
     */
    public function login(?string $id = null, ?string $extra = null): array
    {
        $request = new \App\Application\Requests\LoginRequest();
        $identity = $request->get('identity');
        $password = $request->get('password');

        $result = $this->service->loginUser($identity, $password);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php', 2), '/\\');

        if ($result['mfa_required'] ?? false) {
            return [
                'success' => true,
                'mfa_required' => true,
                'mfa_token' => $result['mfa_token'] ?? '',
                'redirect' => $base . '/public/views/auth/mfa-challenge.php'
            ];
        }

        $user = $result['user'];
        $account = $result['account'];

        \App\Shared\Security\SessionManager::establishUserSession($user, $account);

        // $base is already defined above
        if ($user['status'] === \App\Domain\Enums\AccountStatusEnum::PENDING->value) {
            $redirect = $base . '/public/views/user/complete-profile.php';
        } else {
            $redirect = $base . '/public/views/user/dashboard.php';
        }

        return [
            'success'  => true,
            'message'  => 'Login successful',
            'redirect' => $redirect,
            'user'     => [
                'full_name'    => $user['full_name'],
                'email'        => $user['email'],
                'status'       => $user['status'],
                'account_no'   => $account ? $account['account_no'] : '',
                'account_type' => $account ? $account['account_type'] : 'savings',
                'balance'      => $account ? $account['balance'] : '0'
            ]
        ];
    }

    /**
     * Authenticate a system administrator.
     *
     * @param string|null $id Optional identity override
     * @param string|null $extra Optional extra parameter
     * @return array{success: bool, mfa_required?: bool, mfa_token?: string, redirect: string, message?: string}
     */
    public function adminLogin(?string $id = null, ?string $extra = null): array
    {
        $request = new \App\Application\Requests\LoginRequest();
        $email = $request->get('email') ?? $request->get('identity');
        $password = $request->get('password');

        $result = $this->service->loginAdmin($email, $password);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php', 2), '/\\');

        if ($result['mfa_required'] ?? false) {
            return [
                'success' => true,
                'mfa_required' => true,
                'mfa_token' => $result['mfa_token'] ?? '',
                'redirect' => $base . '/public/views/auth/admin-mfa-challenge.php'
            ];
        }

        \App\Shared\Security\SessionManager::establishAdminSession($result['admin']);

        return [
            'success'  => true,
            'message'  => 'Admin login successful',
            'redirect' => $base . '/public/views/admin/admin-dashboard.php'
        ];
    }

    /**
     * Authenticate a branch staff member.
     *
     * @param string|null $id Optional identity override
     * @param string|null $extra Optional extra parameter
     * @return array{success: bool, mfa_required?: bool, mfa_token?: string, redirect: string, message?: string}
     */
    public function staffLogin(?string $id = null, ?string $extra = null): array
    {
        $request = new \App\Application\Requests\LoginRequest();
        $identity = $request->get('identity');
        $password = $request->get('password');

        $result = $this->service->loginStaff($identity, $password);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php', 2), '/\\');

        if ($result['mfa_required'] ?? false) {
            return [
                'success' => true,
                'mfa_required' => true,
                'mfa_token' => $result['mfa_token'] ?? '',
                'redirect' => $base . '/public/views/auth/staff-mfa-challenge.php'
            ];
        }

        \App\Shared\Security\SessionManager::establishStaffSession($result['staff']);

        return [
            'success'  => true,
            'message'  => 'Login successful',
            'redirect' => $base . '/public/views/staff/staff-dashboard.php'
        ];
    }

    public function forgotPassword(?string $id = null, ?string $extra = null): array
    {
        $email = $this->security->cleanInput($_POST['email'] ?? '');
        $csrfToken = $_POST['csrf_token'] ?? '';

        \App\Presentation\Validators\AuthValidator::validateForgotPassword($email, $csrfToken, $this->security);

        $this->service->forgotPassword($email);

        return [
            'success' => true,
            'message' => 'If an account with this email exists, a password reset link has been sent.'
        ];
    }

    public function resetPassword(?string $id = null, ?string $extra = null): array
    {
        $token            = $this->security->cleanInput($_POST['token'] ?? '');
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        \App\Presentation\Validators\AuthValidator::validateResetPassword($token, $newPassword, $confirmPassword, $csrfToken, $this->security);

        $this->service->resetPassword($token, $newPassword);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php', 2), '/\\');

        return [
            'success'  => true,
            'message'  => 'Password has been reset successfully. You can now log in with your new password.',
            'redirect' => $base . '/public/views/auth/login.php'
        ];
    }

    public function register()
    {
        $request = new \App\Application\Requests\RegisterRequest();
        $fullName = $request->get('full_name');
        $email = $request->get('email');
        $phone = $request->get('phone');
        $password = $request->get('password');

        $this->service->registerUser($fullName, $email, $phone, $password);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php', 2), '/\\');

        return [
            'success'  => true,
            'message'  => 'Registration successful! Please complete your profile.',
            'redirect' => $base . '/public/views/auth/login.php'
        ];
    }

    public function logout(?string $id = null, ?string $extra = null): array
    {
        $role = $_SESSION['role'] ?? '';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php', 2), '/\\');
        \App\Shared\Security\SessionManager::destroy();

        switch ($role) {
            case 'staff':
                $redirect = $base . '/public/views/auth/staff-login.php';
                break;
            case 'admin':
                $redirect = $base . '/public/views/auth/admin-login.php';
                break;
            case 'customer':
            default:
                $redirect = $base . '/public/views/auth/login.php';
                break;
        }

        // If called via browser navigation (GET, no XHR header), redirect directly
        $isAjax = !\App\Shared\Core\Validation\Validator::isEmpty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (!$isAjax && $_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!headers_sent()) {
                header('Location: ' . $redirect, true, 302);
            }
            exit;
        }

        return [
            'success'  => true,
            'message'  => 'Logged out successfully',
            'redirect' => $redirect
        ];
    }

    public function getCsrfToken(?string $id = null, ?string $extra = null): array
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        $token = generate_csrf_token();

        return [
            'success' => true,
            'csrf_token' => $token
        ];
    }
}
