<?php
namespace App\Controllers\Auth;

use App\Services\AuthService;

class AuthController {
    private AuthService $service;

    public function __construct(AuthService $service) {
        $this->service = $service;
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $identity = clean_input($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($identity) || empty($password)) {
            json_response(['success' => false, 'message' => 'Email/Account Number and password are required'], 422);
        }

        $result = $this->service->loginUser($identity, $password);
        
        $user = $result['user'];
        $account = $result['account'];

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
        $redirect = $base . '/views/user/dashboard.html';

        json_response([
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
        ]);
    }

    public function adminLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (empty($csrf_token)) {
            json_response(['success' => false, 'message' => 'Security token missing. Please refresh the page.'], 403);
        }
        if (!validate_csrf_token($csrf_token)) {
            json_response(['success' => false, 'message' => 'Invalid or expired security session. Please try again.'], 403);
        }

        $email    = clean_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            json_response(['success' => false, 'message' => 'Email and password are required'], 422);
        }

        $this->service->loginAdmin($email, $password);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
        $redirect = $base . '/views/admin/admin-dashboard.html';

        json_response([
            'success'  => true,
            'message'  => 'Login successful',
            'redirect' => $redirect
        ]);
    }

    public function staffLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (empty($csrf_token)) {
            json_response(['success' => false, 'message' => 'Security token missing. Please refresh the page.'], 403);
        }
        if (!validate_csrf_token($csrf_token)) {
            json_response(['success' => false, 'message' => 'Invalid or expired security session. Please try again.'], 403);
        }

        $identity = clean_input($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($identity) || empty($password)) {
            json_response(['success' => false, 'message' => 'Email/Staff ID and password are required'], 422);
        }

        $this->service->loginStaff($identity, $password);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
        $redirect = $base . '/views/staff/staff-dashboard.html';

        json_response([
            'success'  => true,
            'message'  => 'Login successful',
            'redirect' => $redirect
        ]);
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            json_response(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.'], 403);
        }

        $email = clean_input($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['success' => false, 'message' => 'Please enter a valid email address'], 422);
        }

        $this->service->forgotPassword($email);
    }

    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            json_response(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.'], 403);
        }

        $token            = clean_input($_POST['token'] ?? '');
        $new_password     = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($token)) {
            json_response(['success' => false, 'message' => 'Reset token is required'], 422);
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
            json_response(['success' => false, 'errors' => $errors], 422);
        }

        $this->service->resetPassword($token, $new_password);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            json_response(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.'], 403);
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
            json_response(['success' => false, 'errors' => $errors], 422);
        }

        $this->service->registerUser($full_name, $email, $phone, $password);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\');
        $redirect = $base . '/views/user/dashboard.html';

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

        if (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        ) {
            json_response([
                'success'  => true,
                'message'  => 'Logged out successfully',
                'redirect' => $redirect
            ]);
        }

        header('Location: ' . APP_URL . $redirect);
        exit;
    }

    public function getCsrfToken() {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        $token = generate_csrf_token();

        json_response([
            'success' => true,
            'csrf_token' => $token
        ]);
    }
}
