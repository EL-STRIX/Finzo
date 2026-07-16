<?php

namespace App\Controllers\Staff;

use App\Services\StaffService;
use Exception;

class StaffController
{
    private StaffService $service;

    public function __construct(StaffService $service)
    {
        $this->service = $service;
    }

    public function accountLookup()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $account_no = clean_input($_GET['account_no'] ?? '');
        if (empty($account_no)) {
            json_response(['success' => false, 'found' => false, 'message' => 'Account number is required'], 422);
        }

        try {
            $data = $this->service->accountLookup($account_no);
            if (!$data['found']) {
                json_response(['success' => true, 'found' => false]);
            }

            json_response([
                'success' => true,
                'found'   => true,
                'account' => $data['account']
            ]);
        } catch (Exception $e) {
            error_log("Account lookup error: " . $e->getMessage());
            json_response(['success' => false, 'found' => false, 'message' => 'Lookup failed'], 500);
        }
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            json_response(['success' => false, 'message' => 'Invalid security token'], 403);
        }

        $staff_id = $_SESSION['staff_id'];
        $current_password  = $_POST['current_password'] ?? '';
        $new_password      = $_POST['new_password'] ?? '';
        $confirm_password  = $_POST['confirm_password'] ?? '';

        $errors = [];
        if (empty($current_password)) {
            $errors['current_password'] = 'Current password is required';
        }
        if (empty($new_password)) {
            $errors['new_password'] = 'New password is required';
        }
        if (empty($confirm_password)) {
            $errors['confirm_password'] = 'Please confirm your new password';
        }

        if (!empty($errors)) {
            json_response(['success' => false, 'errors' => $errors], 422);
        }

        $strength = validate_password_strength($new_password);
        if (!$strength['valid']) {
            json_response(['success' => false, 'errors' => ['new_password' => $strength['message']]], 422);
        }

        if ($new_password !== $confirm_password) {
            json_response(['success' => false, 'errors' => ['confirm_password' => 'Passwords do not match']], 422);
        }

        try {
            $this->service->changePassword($staff_id, $current_password, $new_password);
            json_response([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            if ($code === 401) {
                json_response(['success' => false, 'errors' => ['current_password' => $e->getMessage()]], 401);
            }
            if ($code === 404) {
                json_response(['success' => false, 'message' => $e->getMessage()], 404);
            }
            error_log("Staff change password error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to change password. Please try again.'], 500);
        }
    }

    public function dashboard()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $staff_id = $_SESSION['staff_id'];

        try {
            $data = $this->service->getDashboardData($staff_id);

            json_response([
                'success' => true,
                'staff' => $data['staff'],
                'shift_stats' => $data['shift_stats'],
                'recent_activity' => $data['recent_activity'],
                'greeting' => get_greeting(),
                'csrf_token' => $_SESSION['csrf_token'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Staff dashboard error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load dashboard data'], 500);
        }
    }

    public function deposit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            json_response(['success' => false, 'message' => 'Invalid security token'], 403);
        }

        $staff_id = $_SESSION['staff_id'];
        $account_no     = clean_input($_POST['account_no'] ?? '');
        $amount         = floatval($_POST['amount'] ?? 0);
        $reference_note = clean_input($_POST['reference_note'] ?? '');

        $errors = [];
        if (empty($account_no)) {
            $errors['account_no'] = 'Account number is required';
        }
        if ($amount < 100) {
            $errors['amount'] = 'Minimum deposit amount is ₹100';
        }

        if (!empty($errors)) {
            json_response(['success' => false, 'errors' => $errors], 422);
        }

        try {
            $data = $this->service->processDeposit($staff_id, $account_no, $amount, $reference_note);
            json_response([
                'success'        => true,
                'message'        => 'Deposit processed successfully',
                'transaction_id' => $data['transaction_id'],
                'customer_name'  => $data['customer_name'],
                'account_no'     => $data['account_no'],
                'amount'         => $data['amount'],
                'new_balance'    => $data['new_balance'],
                'redirect'       => '/views/staff/staff-receipt.html?id=' . $data['transaction_id']
            ]);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            if ($code === 404 || $code === 403) {
                json_response(['success' => false, 'message' => $e->getMessage()], $code);
            }
            error_log("Staff deposit error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to process deposit. Please try again.'], 500);
        }
    }

    public function history()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $staff_id = $_SESSION['staff_id'];

        $filters = [
            'type'      => clean_input($_GET['type'] ?? 'all'),
            'status'    => clean_input($_GET['status'] ?? 'all'),
            'search'    => clean_input($_GET['search'] ?? ''),
            'from_date' => clean_input($_GET['from_date'] ?? ''),
            'to_date'   => clean_input($_GET['to_date'] ?? ''),
            'page'      => max(1, intval($_GET['page'] ?? 1)),
            'per_page'  => intval($_GET['per_page'] ?? 20)
        ];

        if ($filters['per_page'] !== 999) {
            $filters['per_page'] = min(50, max(10, $filters['per_page']));
        }

        try {
            $data = $this->service->getHistory($staff_id, $filters);
            json_response([
                'success'      => true,
                'transactions' => $data['transactions'],
                'total'        => $data['total'],
                'pages'        => $data['pages'],
                'current_page' => $filters['page'],
                'per_page'     => $filters['per_page']
            ]);
        } catch (Exception $e) {
            error_log("Staff history error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load transaction history'], 500);
        }
    }

    public function receipt()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $txn_id = clean_input($_GET['id'] ?? $_GET['txn_id'] ?? '');
        if (empty($txn_id)) {
            json_response(['success' => false, 'message' => 'Transaction ID is required'], 422);
        }

        try {
            $transaction = $this->service->getReceipt($txn_id);

            $customer_name = '';
            $account_no    = '';
            if ($transaction['type'] === 'deposit') {
                $customer_name = $transaction['to_name'];
                $account_no    = $transaction['to_account_no'];
            } elseif ($transaction['type'] === 'withdrawal') {
                $customer_name = $transaction['from_name'];
                $account_no    = $transaction['from_account_no'];
            }

            json_response([
                'success'     => true,
                'transaction' => [
                    'txn_id'         => $transaction['txn_id'],
                    'type'           => $transaction['type'],
                    'amount'         => (float)$transaction['amount'],
                    'balance_after'  => (float)$transaction['balance_after'],
                    'description'    => $transaction['description'],
                    'reference_note' => $transaction['reference_note'],
                    'status'         => $transaction['status'],
                    'date'           => $transaction['created_at'],
                    'customer_name'  => $customer_name,
                    'account_no'     => $account_no,
                    'from_account'   => $transaction['from_account_no'],
                    'from_name'      => $transaction['from_name'],
                    'to_account'     => $transaction['to_account_no'],
                    'to_name'        => $transaction['to_name'],
                    'staff_id'       => $transaction['staff_display_id'],
                    'staff_name'     => $transaction['staff_name'],
                    'branch_name'    => $transaction['branch_name']
                ]
            ]);
        } catch (Exception $e) {
            if ($e->getCode() === 404) {
                json_response(['success' => false, 'message' => $e->getMessage()], 404);
            }
            error_log("Staff receipt error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to load transaction receipt'], 500);
        }
    }

    public function profile()
    {
        $staff_id = $_SESSION['staff_id'];
        $method   = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            try {
                $data = $this->service->getFullProfile($staff_id);
                json_response([
                    'success'   => true,
                    'staff'     => $data['staff'],
                    'address'   => $data['address'],
                    'documents' => $data['documents']
                ]);
            } catch (Exception $e) {
                if ($e->getCode() === 404) {
                    json_response(['success' => false, 'message' => $e->getMessage()], 404);
                }
                error_log("Staff profile fetch error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'Failed to load profile data'], 500);
            }
        }

        if ($method === 'POST') {
            if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
                json_response(['success' => false, 'message' => 'Invalid security token'], 403);
            }

            $phone                   = clean_input($_POST['phone'] ?? '');
            $emergency_contact_name  = clean_input($_POST['emergency_contact_name'] ?? '');
            $emergency_contact_phone = clean_input($_POST['emergency_contact_phone'] ?? '');

            $errors = [];
            if ($phone !== '' && !validate_phone($phone)) {
                $errors['phone'] = 'Enter a valid 10-digit mobile number';
            }
            if ($emergency_contact_phone !== '' && !validate_phone($emergency_contact_phone)) {
                $errors['emergency_contact_phone'] = 'Enter a valid 10-digit emergency contact number';
            }

            $file = null;
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['profile_photo'];
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);
                if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
                    $errors['profile_photo'] = 'Only JPEG and PNG images are allowed';
                }
                if ($file['size'] > MAX_UPLOAD_SIZE) {
                    $errors['profile_photo'] = 'Profile photo must be under 5 MB';
                }
            }

            if (!empty($errors)) {
                json_response(['success' => false, 'errors' => $errors], 422);
            }

            try {
                $updated = $this->service->updateProfile($staff_id, [
                    'phone' => $phone,
                    'emergency_contact_name' => $emergency_contact_name,
                    'emergency_contact_phone' => $emergency_contact_phone
                ], $file);

                json_response([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'staff'   => $updated
                ]);
            } catch (Exception $e) {
                if ($e->getCode() === 422) {
                    json_response(['success' => false, 'message' => $e->getMessage()], 422);
                }
                error_log("Staff profile update error: " . $e->getMessage());
                json_response(['success' => false, 'message' => 'Failed to update profile'], 500);
            }
        }

        json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    public function withdrawal()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            json_response(['success' => false, 'message' => 'Invalid security token'], 403);
        }

        $staff_id = $_SESSION['staff_id'];
        $account_no     = clean_input($_POST['account_no'] ?? '');
        $amount         = floatval($_POST['amount'] ?? 0);
        $identity_notes = clean_input($_POST['identity_notes'] ?? '');

        $errors = [];
        if (empty($account_no)) {
            $errors['account_no'] = 'Account number is required';
        }
        if ($amount <= 0) {
            $errors['amount'] = 'Amount must be greater than zero';
        }

        if (!empty($errors)) {
            json_response(['success' => false, 'errors' => $errors], 422);
        }

        try {
            $data = $this->service->processWithdrawal($staff_id, $account_no, $amount, $identity_notes);
            json_response([
                'success'        => true,
                'message'        => 'Withdrawal processed successfully',
                'transaction_id' => $data['transaction_id'],
                'customer_name'  => $data['customer_name'],
                'account_no'     => $data['account_no'],
                'amount'         => $data['amount'],
                'new_balance'    => $data['new_balance'],
                'redirect'       => '/views/staff/staff-receipt.html?id=' . $data['transaction_id']
            ]);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            if (in_array($code, [403, 404, 422])) {
                json_response(['success' => false, 'message' => $e->getMessage()], $code);
            }
            error_log("Staff withdrawal error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to process withdrawal. Please try again.'], 500);
        }
    }
}
