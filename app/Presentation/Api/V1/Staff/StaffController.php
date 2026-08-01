<?php

namespace App\Presentation\Api\V1\Staff;

use App\Shared\Security\SecurityService;

use App\Application\Services\StaffService;
use Exception;

class StaffController
{
    protected SecurityService $security;

    private StaffService $service;

    public function __construct(StaffService $service, ?SecurityService $security = null)
    {
        $this->security = $security ?? new \App\Shared\Security\SecurityService();
        $this->service = $service;
    }

    public function accountLookup()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $accountNumber = $this->security->cleanInput($_GET['account_no'] ?? '');
        if (\App\Shared\Core\Validation\Validator::isEmpty($accountNumber)) {
            json_response(['success' => false, 'found' => false, 'message' => 'Account number is required'], 422);
        }

        try {
            $data = $this->service->accountLookup($accountNumber);
            if (!$data['found']) {
                json_response(['success' => true, 'found' => false]);
            }

            json_response([
                'success' => true,
                'found'   => true,
                'account' => $data['account']
            ]);
        } catch (Exception $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Account lookup error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'found' => false, 'message' => 'Lookup failed'], 500);
        }
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }



        $staffId = $_SESSION['staff_id'];
        $validator = new \App\Presentation\Validators\StaffValidator();
        $validated = $validator->validatePasswordUpdate($_POST);
        $currentPassword = $validated['current_password'];
        $newPassword = $validated['new_password'];


        $strength = validate_password_strength($newPassword);
        if (!$strength['valid']) {
            json_response(['success' => false, 'errors' => ['new_password' => $strength['message']]], 422);
        }

        if ($newPassword !== $confirmPassword) {
            json_response(['success' => false, 'errors' => ['confirm_password' => 'Passwords do not match']], 422);
        }

        try {
            $this->service->changePassword($staffId, $currentPassword, $newPassword);
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
            \App\Shared\Core\Logging\Logger::getInstance()->error('Staff change password error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to change password. Please try again.'], 500);
        }
    }

    public function dashboard()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $staffId = $_SESSION['staff_id'];

        try {
            $data = $this->service->getDashboardData($staffId);

            json_response([
                'success' => true,
                'staff' => $data['staff'],
                'shift_stats' => $data['shift_stats'],
                'recent_activity' => $data['recent_activity'],
                'greeting' => get_greeting(),
                'csrf_token' => $_SESSION['csrf_token'] ?? ''
            ]);
        } catch (Exception $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Staff dashboard error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to load dashboard data'], 500);
        }
    }

    public function deposit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }



        $staffId = $_SESSION['staff_id'];
        $validator = new \App\Presentation\Validators\StaffValidator();
        $validated = $validator->validateDeposit($_POST);
        $accountNumber = $validated['account_no'];
        $amount = $validated['amount'];
        $reference_note = $validated['reference_note'];


        try {
            $data = $this->service->processDeposit($staffId, $accountNumber, $amount, $reference_note);
            json_response([
                'success'        => true,
                'message'        => 'Deposit processed successfully',
                'transaction_id' => $data['transaction_id'],
                'customer_name'  => $data['customer_name'],
                'account_no'     => $data['account_no'],
                'amount'         => $data['amount'],
                'new_balance'    => $data['new_balance'],
                'redirect'       => '/public/views/staff/staff-receipt.html?id=' . $data['transaction_id']
            ]);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            if ($code === 404 || $code === 403) {
                json_response(['success' => false, 'message' => $e->getMessage()], $code);
            }
            \App\Shared\Core\Logging\Logger::getInstance()->error('Staff deposit error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to process deposit. Please try again.'], 500);
        }
    }

    public function history()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $staffId = $_SESSION['staff_id'];

        $filters = [
            'type'      => $this->security->cleanInput($_GET['type'] ?? 'all'),
            'status'    => $this->security->cleanInput($_GET['status'] ?? 'all'),
            'search'    => $this->security->cleanInput($_GET['search'] ?? ''),
            'from_date' => $this->security->cleanInput($_GET['from_date'] ?? ''),
            'to_date'   => $this->security->cleanInput($_GET['to_date'] ?? ''),
            'page'      => max(1, intval($_GET['page'] ?? 1)),
            'per_page'  => intval($_GET['per_page'] ?? 20)
        ];

        if ($filters['per_page'] !== 999) {
            $filters['per_page'] = min(50, max(10, $filters['per_page']));
        }

        try {
            $data = $this->service->getHistory($staffId, $filters);
            json_response([
                'success'      => true,
                'transactions' => $data['transactions'],
                'total'        => $data['total'],
                'pages'        => $data['pages'],
                'current_page' => $filters['page'],
                'per_page'     => $filters['per_page']
            ]);
        } catch (Exception $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Staff history error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to load transaction history'], 500);
        }
    }

    public function receipt()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $transactionId = $this->security->cleanInput($_GET['id'] ?? $_GET['txn_id'] ?? '');
        if (\App\Shared\Core\Validation\Validator::isEmpty($transactionId)) {
            json_response(['success' => false, 'message' => 'Transaction ID is required'], 422);
        }

        try {
            $transaction = $this->service->getReceipt($transactionId);

            $customer_name = '';
            $accountNumber    = '';
            if ($transaction['type'] === 'deposit') {
                $customer_name = $transaction['to_name'];
                $accountNumber    = $transaction['to_account_no'];
            } elseif ($transaction['type'] === 'withdrawal') {
                $customer_name = $transaction['from_name'];
                $accountNumber    = $transaction['from_account_no'];
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
                    'account_no'     => $accountNumber,
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
            \App\Shared\Core\Logging\Logger::getInstance()->error('Staff receipt error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to load transaction receipt'], 500);
        }
    }

    public function profile()
    {
        $staffId = $_SESSION['staff_id'];
        $method   = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            try {
                $data = $this->service->getFullProfile($staffId);
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
                \App\Shared\Core\Logging\Logger::getInstance()->error('Staff profile fetch error: ', ['context' => $e->getMessage()]);
                json_response(['success' => false, 'message' => 'Failed to load profile data'], 500);
            }
        }

        if ($method === 'POST') {


            $file = (\App\Shared\Core\Validation\Validator::isSet($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) ? $_FILES['profile_photo'] : null;
            
            $validator = new \App\Presentation\Validators\StaffValidator();
            $validated = $validator->validateProfileUpdate($_POST, $file);
            $phone = $validated['phone'];
            $emergency_contact_name = $validated['emergency_contact_name'];
            $emergency_contact_phone = $validated['emergency_contact_phone'];


            try {
                $updated = $this->service->updateProfile($staffId, [
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
                \App\Shared\Core\Logging\Logger::getInstance()->error('Staff profile update error: ', ['context' => $e->getMessage()]);
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



        $staffId = $_SESSION['staff_id'];
        $validator = new \App\Presentation\Validators\StaffValidator();
        $validated = $validator->validateWithdrawal($_POST);
        $accountNumber = $validated['account_no'];
        $amount = $validated['amount'];
        $identity_notes = $validated['identity_notes'];


        try {
            $data = $this->service->processWithdrawal($staffId, $accountNumber, $amount, $identity_notes);
            json_response([
                'success'        => true,
                'message'        => 'Withdrawal processed successfully',
                'transaction_id' => $data['transaction_id'],
                'customer_name'  => $data['customer_name'],
                'account_no'     => $data['account_no'],
                'amount'         => $data['amount'],
                'new_balance'    => $data['new_balance'],
                'redirect'       => '/public/views/staff/staff-receipt.html?id=' . $data['transaction_id']
            ]);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            if (in_array($code, [403, 404, 422])) {
                json_response(['success' => false, 'message' => $e->getMessage()], $code);
            }
            \App\Shared\Core\Logging\Logger::getInstance()->error('Staff withdrawal error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to process withdrawal. Please try again.'], 500);
        }
    }
}
