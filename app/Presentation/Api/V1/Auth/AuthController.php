<?php

namespace App\Presentation\Api\V1\Auth;

use App\Shared\Security\SecurityService;

use App\Application\Services\AuthService;
use App\Shared\Core\Exceptions\ApiException;

class AuthController
{
    protected SecurityService $security;

    private AuthService $service;

    public function __construct(AuthService $service, ?SecurityService $security = null)
    {
        $this->security = $security ?? new \App\Shared\Security\SecurityService();
        $this->service = $service;
    }

    public function login(?string $id = null, ?string $extra = null): array
    {
        $identity = $this->security->cleanInput($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        \App\Presentation\Validators\AuthValidator::validateCustomerLogin($identity, $password, $csrfToken, $this->security);

        $result = $this->service->loginUser($identity, $password);

        $user = $result['user'];
        $account = $result['account'];

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php', 3), '/\\');

        if ($user['status'] === 'pending') {
            $redirect = $base . '/views/user/complete-profile.html';
        } else {
            $redirect = $base . '/views/user/dashboard.html';
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

    public function adminLogin(?string $id = null, ?string $extra = null): array
    {
        $email    = $this->security->cleanInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        \App\Presentation\Validators\AuthValidator::validateLogin($email, $password, $csrfToken);

        $this->service->loginAdmin($email, $password);

        return [
            'success'  => true,
            'message'  => 'Login successful',
            'redirect' => rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\') . '/views/admin/admin-dashboard.html'
        ];
    }

    public function staffLogin(?string $id = null, ?string $extra = null): array
    {
        $identity = $this->security->cleanInput($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        \App\Presentation\Validators\AuthValidator::validateLogin($identity, $password, $csrfToken);

        $this->service->loginStaff($identity, $password);

        return [
            'success'  => true,
            'message'  => 'Login successful',
            'redirect' => rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\') . '/views/staff/staff-dashboard.html'
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

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php', 3), '/\\');

        return [
            'success'  => true,
            'message'  => 'Password has been reset successfully. You can now log in with your new password.',
            'redirect' => $base . '/views/auth/login.html'
        ];
    }

    public function register()
    {
        $fullName        = $this->security->cleanInput($_POST['full_name'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $phone            = $this->security->cleanInput($_POST['phone'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        \App\Presentation\Validators\AuthValidator::validateRegistration($fullName, $email, $phone, $password, $confirmPassword, $csrfToken, $this->security);

        $this->service->registerUser($fullName, $email, $phone, $password);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php', 3), '/\\');

        return [
            'success'  => true,
            'message'  => 'Registration successful! Please complete your profile.',
            'redirect' => $base . '/views/auth/login.html'
        ];
    }

    public function logout(?string $id = null, ?string $extra = null): array
    {
        $role = $_SESSION['role'] ?? '';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php', 3), '/\\');
        destroy_session();

        switch ($role) {
            case 'staff':
                $redirect = $base . '/views/auth/staff-login.html';
                break;
            case 'admin':
                $redirect = $base . '/views/auth/admin-login.html';
                break;
            case 'customer':
            default:
                $redirect = $base . '/views/auth/login.html';
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
