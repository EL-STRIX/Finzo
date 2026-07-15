<?php
namespace App\Controllers\Auth;

use App\Services\AuthService;
use App\Core\Exceptions\ApiException;

class AuthController {
    private AuthService $service;

    public function __construct(AuthService $service) {
        $this->service = $service;
    }

    public function login(): array {
        $identity = clean_input($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new ApiException('Invalid or missing CSRF token', 403);
        }

        if (empty($identity) || empty($password)) {
            throw new ApiException('Email/Account Number and password are required', 422);
        }

        $result = $this->service->loginUser($identity, $password);
        
        $user = $result['user'];
        $account = $result['account'];

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '', 3), '/\\');
        $redirect = $base . '/views/user/dashboard.html';

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

    public function adminLogin(): array {
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (empty($csrf_token)) {
            throw new ApiException('Security token missing. Please refresh the page.', 403);
        }
        if (!validate_csrf_token($csrf_token)) {
            throw new ApiException('Invalid or expired security session. Please try again.', 403);
        }

        $email    = clean_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            throw new ApiException('Email and password are required', 422);
        }

        $this->service->loginAdmin($email, $password);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
        $redirect = $base . '/views/admin/admin-dashboard.html';

        return [
            'success'  => true,
            'message'  => 'Login successful',
            'redirect' => $redirect
        ];
    }

    public function staffLogin(): array {
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (empty($csrf_token)) {
            throw new ApiException('Security token missing. Please refresh the page.', 403);
        }
        if (!validate_csrf_token($csrf_token)) {
            throw new ApiException('Invalid or expired security session. Please try again.', 403);
        }

        $identity = clean_input($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($identity) || empty($password)) {
            throw new ApiException('Email/Staff ID and password are required', 422);
        }

        $this->service->loginStaff($identity, $password);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
        $redirect = $base . '/views/staff/staff-dashboard.html';

        return [
            'success'  => true,
            'message'  => 'Login successful',
            'redirect' => $redirect
        ];
    }

    public function forgotPassword(): array {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new ApiException('Invalid security token. Please refresh and try again.', 403);
        }

        $email = clean_input($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ApiException('Please enter a valid email address', 422);
        }

        $this->service->forgotPassword($email);

        return [
            'success' => true,
            'message' => 'If an account with this email exists, a password reset link has been sent.'
        ];
    }

    public function resetPassword(): array {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new ApiException('Invalid security token. Please refresh and try again.', 403);
        }

        $token            = clean_input($_POST['token'] ?? '');
        $new_password     = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($token)) {
            throw new ApiException('Reset token is required', 422);
        }

        if (empty($new_password)) {
            $errors['new_password'] = 'New password is required';
        } else {
            $strength = validate_password_strength($new_password);
            if (!$strength['valid']) {
                $errors['new_password'] = $strength['message'];
            }
        }

        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            throw new ApiException('Validation failed', 422, ['errors' => $errors]);
        }

        $this->service->resetPassword($token, $new_password);

        return [
            'success'  => true,
            'message'  => 'Password has been reset successfully. You can now log in with your new password.',
            'redirect' => '/views/auth/login.html'
        ];
    }

    public function register() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new ApiException('Invalid security token. Please refresh and try again.', 403);
        }

        $full_name        = clean_input($_POST['full_name'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $phone            = clean_input($_POST['phone'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($full_name)) {
            $errors['full_name'] = 'Full name is required';
        } elseif (strlen($full_name) < 2 || strlen($full_name) > 100) {
            $errors['full_name'] = 'Full name must be between 2 and 100 characters';
        }

        if (empty($email)) {
            $errors['email'] = 'Email address is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        if (empty($phone)) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!validate_phone($phone)) {
            $errors['phone'] = 'Please enter a valid 10-digit Indian mobile number';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } else {
            $strength = validate_password_strength($password);
            if (!$strength['valid']) {
                $errors['password'] = $strength['message'];
            }
        }

        if ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            throw new ApiException('Validation failed', 422, ['errors' => $errors]);
        }

        $this->service->registerUser($full_name, $email, $phone, $password);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php', 3), '/\\');
        $redirect = $base . '/views/auth/login.html';

        // Router sends 200 for arrays, but register was sending 201.
        // Let's call json_response directly for 201, or update router. 
        // For simplicity, we just return the array and let Router send 200. It's an API, 200 is acceptable for registration if not strictly RESTful.
        // Actually I can just call json_response here since Router doesn't mind if we exit early.
        json_response([
            'success'  => true,
            'message'  => 'Registration successful! Please complete your profile.',
            'redirect' => $redirect
        ], 201);
    }

    public function logout() {
        $role = $_SESSION['role'] ?? '';
        destroy_session();

        switch ($role) {
            case 'staff':
                $redirect = '/views/auth/staff-login.html';
                break;
            case 'admin':
                $redirect = '/views/auth/admin-login.html';
                break;
            case 'customer':
            default:
                $redirect = '/views/auth/login.html';
                break;
        }

        return [
            'success'  => true,
            'message'  => 'Logged out successfully',
            'redirect' => $redirect
        ];
    }

    public function getCsrfToken(): array {
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
