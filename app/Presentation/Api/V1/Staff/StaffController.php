<?php

namespace App\Presentation\Api\V1\Staff;

use App\Shared\Security\SecurityService;

use App\Application\Services\StaffService;
use Exception;

class StaffController
{
    protected SecurityService $security;

    private StaffService $service;

    public function __construct(\App\Domain\Contracts\Services\StaffServiceInterface $service, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security;
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

        try {
            $this->service->changePassword($staffId, $currentPassword, $newPassword);
            json_response([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } catch (\Exception $e) {
            throw $e;
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
        } catch (\Exception $e) {
            throw $e;
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
        } catch (\Exception $e) {
            throw $e;
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
            if ($transaction['type'] === \App\Domain\Enums\TransactionTypeEnum::DEPOSIT->value) {
                $customer_name = $transaction['to_name'];
                $accountNumber    = $transaction['to_account_no'];
            } elseif ($transaction['type'] === \App\Domain\Enums\TransactionTypeEnum::WITHDRAWAL->value) {
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
        } catch (\Exception $e) {
            throw $e;
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
            } catch (\Exception $e) {
                throw $e;
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
            } catch (\Exception $e) {
                throw $e;
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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function clockIn()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $staffId = $_SESSION['staff_id'];
        try {
            $this->service->clockIn($staffId);
            json_response(['success' => true, 'message' => 'Successfully clocked in']);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function clockOut()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $staffId = $_SESSION['staff_id'];
        $notes = $this->security->cleanInput($_POST['notes'] ?? '');
        
        try {
            $this->service->clockOut($staffId, $notes);
            json_response(['success' => true, 'message' => 'Successfully clocked out']);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
