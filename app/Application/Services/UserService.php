<?php

namespace App\Application\Services;

use App\Infrastructure\Repositories\UserRepository;
use Exception;
use DateTime;

class UserService implements \App\Domain\Contracts\Services\UserServiceInterface
{
    protected \App\Domain\Contracts\Repositories\UserRepositoryInterface $repository;
    /**
     *   construct.
     *
     * @param \App\Domain\Contracts\Repositories\UserRepositoryInterface $repository
     * @param ?\App\Domain\Contracts\Security\SecurityServiceInterface $security
     */
    public function __construct(\App\Domain\Contracts\Repositories\UserRepositoryInterface $repository, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->repository = $repository;
    }
    /**
     * Get Repository.
     *
     * @return UserRepository
     */
    public function getRepository(): \App\Domain\Contracts\Repositories\UserRepositoryInterface
    {
        return $this->repository;
    }
    /**
     * Validate Kyc.
     *
     * @param array $data
     * @param array $files
     * @return mixed
     */
    public function validateKyc(array $data, array $files)
    {
        $errors = [];
        $dob = $data['dob'];
        if (empty($dob)) {
            $errors['dob'] = 'Date of birth is required';
        } else {
            $dob_date = new \DateTime($dob);
            $today = new \DateTime();
            $age = $dob_date->diff($today)->y;
            if ($age < 18) {
                $errors['dob'] = 'You must be at least 18 years old';
            }
        }

        $gender = $data['gender'];
        if (empty($gender) || !in_array($gender, ['Male', 'Female', 'Other', 'Prefer not to say'])) {
            $errors['gender'] = 'Please select a valid gender';
        }

        $father_name = $data['father_name'];
        if (empty($father_name) || strlen($father_name) < 2) {
            $errors['father_name'] = "Father's name is required";
        }

        $mother_name = $data['mother_name'];
        if (empty($mother_name) || strlen($mother_name) < 2) {
            $errors['mother_name'] = "Mother's name is required";
        }

        $marital_status = $data['marital_status'];
        if (empty($marital_status) || !in_array($marital_status, ['Single', 'Married', 'Divorced', 'Widowed', 'Prefer not to say'])) {
            $errors['marital_status'] = 'Please select a valid marital status';
        }

        $nationality = $data['nationality'];
        if (empty($nationality)) {
            $errors['nationality'] = 'Nationality is required';
        }

        $occupation = $data['occupation'];
        if (empty($occupation)) {
            $errors['occupation'] = 'Occupation is required';
        }

        $source_of_income = $data['source_of_income'];
        if (empty($source_of_income)) {
            $errors['source_of_income'] = 'Source of income is required';
        }

        $annual_income = $data['annual_income'];
        if (empty($annual_income)) {
            $errors['annual_income'] = 'Annual income range is required';
        }

        $street = $data['street'];
        if (empty($street)) {
            $errors['street'] = 'Street address is required';
        }

        $city = $data['city'];
        if (empty($city)) {
            $errors['city'] = 'City is required';
        }

        $state = $data['state'];
        if (empty($state)) {
            $errors['state'] = 'State is required';
        }

        $pincode = $data['pincode'];
        if (empty($pincode) || !validate_pincode($pincode)) {
            $errors['pincode'] = 'Please enter a valid 6-digit pincode';
        }

        $id_type = $data['id_type'];
        $valid_id_types = ['Aadhaar', 'PAN', 'Passport', 'Voter ID', 'Driving License'];
        if (empty($id_type) || !in_array($id_type, $valid_id_types)) {
            $errors['id_type'] = 'Please select a valid document type';
        }

        $id_number = $data['id_number'];
        if (empty($id_number)) {
            $errors['id_number'] = 'Document number is required';
        } elseif (!empty($id_type) && in_array($id_type, $valid_id_types)) {
            if ($this->checkIdNumberExists($id_type, $id_number, $data['user_id'] ?? 0)) {
                $errors['id_number'] = 'This document number is already registered';
            }
        }

        $has_profile_pic = !empty($data['has_profile_pic']);
        $has_id_front = !empty($data['has_id_front']);
        $has_id_back = !empty($data['has_id_back']);

        if (!isset($files['profile_picture']) || $files['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            if (!$has_profile_pic) {
                $errors['profile_picture'] = 'Profile photo is required';
            }
        } else {
            $profile = $files['profile_picture'];
            if ($profile['size'] > MAX_UPLOAD_SIZE) {
                $errors['profile_picture'] = 'Profile photo must be less than 5MB';
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $profile['tmp_name']);
            if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
                $errors['profile_picture'] = 'Profile photo must be JPG or PNG format';
            }
        }

        if (!isset($files['id_front']) || $files['id_front']['error'] !== UPLOAD_ERR_OK) {
            if (!$has_id_front) {
                $errors['id_front'] = 'Front image of identity document is required';
            }
        } else {
            $front = $files['id_front'];
            if ($front['size'] > MAX_UPLOAD_SIZE) {
                $errors['id_front'] = 'Front image must be less than 5MB';
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $front['tmp_name']);
            if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
                $errors['id_front'] = 'Front image must be JPG or PNG format';
            }
        }

        if (!isset($files['id_back']) || $files['id_back']['error'] !== UPLOAD_ERR_OK) {
            if (!$has_id_back) {
                $errors['id_back'] = 'Back image of identity document is required';
            }
        } else {
            $back = $files['id_back'];
            if ($back['size'] > MAX_UPLOAD_SIZE) {
                $errors['id_back'] = 'Back image must be less than 5MB';
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $back['tmp_name']);
            if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
                $errors['id_back'] = 'Back image must be JPG or PNG format';
            }
        }

        $account_type = $data['account_type'];
        if (empty($account_type) || !in_array($account_type, ['savings', 'current'])) {
            $errors['account_type'] = 'Please select a valid account type';
        }

        $initial_deposit = $data['initial_deposit'];
        $min_deposit = get_setting($this->repository->getPDO(), 'min_initial_deposit', 500);
        if ($initial_deposit < $min_deposit) {
            $errors['initial_deposit'] = 'Initial deposit must be at least ' . CURRENCY_SYMBOL . $min_deposit;
        }

        $transactionPin = $data['txn_pin'];
        if (empty($transactionPin) || !preg_match('/^\d{4}$/', $transactionPin)) {
            $errors['txn_pin'] = 'Transaction PIN must be exactly 4 digits';
        }

        return $errors;
    }
    /**
     * Process Kyc Uploads.
     *
     * @param mixed $files
     * @param mixed $userId
     * @param mixed $existing_files
     * @return mixed
     */
    public function processKycUploads($files, $userId, $existing_files)
    {
        if (!is_dir(STORAGE_KYC_PATH)) {
            mkdir(STORAGE_KYC_PATH, 0755, true);
        }
        $profile_upload_path = UPLOAD_PROFILES_PATH;
        if (!is_dir($profile_upload_path)) {
            mkdir($profile_upload_path, 0755, true);
        }

        $get_ext = function ($file_arr) {
            if (isset($file_arr) && $file_arr['error'] === UPLOAD_ERR_OK) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file_arr['tmp_name']);
                return ($mime === 'image/jpeg') ? 'jpg' : 'png';
            }
            return '';
        };

        $profile_ext = $get_ext($files['profile_picture'] ?? null);
        $front_ext   = $get_ext($files['id_front'] ?? null);
        $back_ext    = $get_ext($files['id_back'] ?? null);

        $profile_filename = $profile_ext ? 'profile_' . $userId . '_' . time() . '.' . $profile_ext : '';
        $front_filename = $front_ext ? 'id_front_' . $userId . '_' . time() . '.' . $front_ext : '';
        $back_filename  = $back_ext ? 'id_back_' . $userId . '_' . time() . '.' . $back_ext : '';

        $profile_destination = $profile_filename ? $profile_upload_path . $profile_filename : '';
        $front_destination = $front_filename ? STORAGE_KYC_PATH . $front_filename : '';
        $back_destination  = $back_filename ? STORAGE_KYC_PATH . $back_filename : '';

        if (isset($files['profile_picture']) && $files['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploaded = (defined('FINZO_TESTING') && FINZO_TESTING) ? copy($files['profile_picture']['tmp_name'], $profile_destination) : move_uploaded_file($files['profile_picture']['tmp_name'], $profile_destination);
            if (!$uploaded) {
                throw new Exception('Failed to upload profile photo');
            }
        }
        if (isset($files['id_front']) && $files['id_front']['error'] === UPLOAD_ERR_OK) {
            $uploaded = (defined('FINZO_TESTING') && FINZO_TESTING) ? copy($files['id_front']['tmp_name'], $front_destination) : move_uploaded_file($files['id_front']['tmp_name'], $front_destination);
            if (!$uploaded) {
                throw new Exception('Failed to upload front document image');
            }
        }
        if (isset($files['id_back']) && $files['id_back']['error'] === UPLOAD_ERR_OK) {
            $uploaded = (defined('FINZO_TESTING') && FINZO_TESTING) ? copy($files['id_back']['tmp_name'], $back_destination) : move_uploaded_file($files['id_back']['tmp_name'], $back_destination);
            if (!$uploaded) {
                throw new Exception('Failed to upload back document image');
            }
        }

        $profile_pic_path = (isset($files['profile_picture']) && $files['profile_picture']['error'] === UPLOAD_ERR_OK)
            ? 'uploads/profiles/' . $profile_filename
            : $existing_files['profile_picture'];

        $id_front_path = (isset($files['id_front']) && $files['id_front']['error'] === UPLOAD_ERR_OK)
            ? 'storage/kyc-documents/' . $front_filename
            : $existing_files['id_front_path'];

        $id_back_path = (isset($files['id_back']) && $files['id_back']['error'] === UPLOAD_ERR_OK)
            ? 'storage/kyc-documents/' . $back_filename
            : $existing_files['id_back_path'];

        return [
            'profile_pic_path' => $profile_pic_path,
            'id_front_path' => $id_front_path,
            'id_back_path' => $id_back_path,
            'front_destination' => $front_destination,
            'back_destination' => $back_destination
        ];
    }
    /**
     * Fetch Account Lookup.
     *
     * @param mixed $args
     * @return mixed
     */
    public function fetchAccountLookup(...$args)
    {
        return $this->repository->fetchAccountLookup(...$args);
    }
    /**
     * Fetch Account Settings.
     *
     * @param mixed $args
     * @return mixed
     */
    public function fetchAccountSettings(...$args)
    {
        return $this->repository->fetchAccountSettings(...$args);
    }
    /**
     * Fetch Used Today.
     *
     * @param mixed $args
     * @return mixed
     */
    public function fetchUsedToday(...$args)
    {
        return $this->repository->fetchUsedToday(...$args);
    }
    /**
     * Update Daily Limit.
     *
     * @param mixed $args
     * @return mixed
     */
    public function updateDailyLimit(...$args)
    {
        return $this->repository->updateDailyLimit(...$args);
    }
    /**
     * Fetch Account Details.
     *
     * @param mixed $args
     * @return mixed
     */
    public function fetchAccountDetails(...$args)
    {
        return $this->repository->fetchAccountDetails(...$args);
    }
    /**
     * Fetch Mini Statement.
     *
     * @param mixed $args
     * @return mixed
     */
    public function fetchMiniStatement(...$args)
    {
        return $this->repository->fetchMiniStatement(...$args);
    }
    /**
     * Fetch Today Transfer Total.
     *
     * @param mixed $args
     * @return mixed
     */
    public function fetchTodayTransferTotal(...$args)
    {
        return $this->repository->fetchTodayTransferTotal(...$args);
    }
    /**
     * Get Customer Id.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getCustomerId(...$args)
    {
        return $this->repository->getCustomerId(...$args);
    }
    /**
     * Fetch Beneficiaries.
     *
     * @param mixed $args
     * @return mixed
     */
    public function fetchBeneficiaries(...$args)
    {
        return $this->repository->fetchBeneficiaries(...$args);
    }
    /**
     * Delete Beneficiary.
     *
     * @param mixed $args
     * @return mixed
     */
    public function deleteBeneficiary(...$args)
    {
        return $this->repository->deleteBeneficiary(...$args);
    }
    /**
     * Get Db.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getDb(...$args)
    {
        if (method_exists($this->repository, 'getDb')) {
            return $this->repository->getDb(...$args);
        }
        return $this->repository->getPDO();
    }
    /**
     * Count Beneficiaries.
     *
     * @param mixed $args
     * @return mixed
     */
    public function countBeneficiaries(...$args)
    {
        return $this->repository->countBeneficiaries(...$args);
    }
    /**
     * Get Beneficiary By Account.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getBeneficiaryByAccount(...$args)
    {
        return $this->repository->getBeneficiaryByAccount(...$args);
    }
    /**
     * Add Beneficiary.
     *
     * @param mixed $args
     * @return mixed
     */
    public function addBeneficiary(...$args)
    {
        return $this->repository->addBeneficiary(...$args);
    }
    /**
     * Get User Password Hash.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getUserPasswordHash(...$args)
    {
        return $this->repository->getUserPasswordHash(...$args);
    }
    /**
     * Update Password.
     *
     * @param mixed $args
     * @return mixed
     */
    public function updatePassword(...$args)
    {
        return $this->repository->updatePassword(...$args);
    }
    /**
     * Get User Files.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getUserFiles(...$args)
    {
        return $this->repository->getUserFiles(...$args);
    }
    /**
     * Get Existing Account By Customer Id.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getExistingAccountByCustomerId(...$args)
    {
        return $this->repository->getExistingAccountByCustomerId(...$args);
    }
    /**
     * Update Account Resubmission.
     *
     * @param mixed $args
     * @return mixed
     */
    public function updateAccountResubmission(...$args)
    {
        return $this->repository->updateAccountResubmission(...$args);
    }
    /**
     * Insert Pending Account.
     *
     * @param mixed $args
     * @return mixed
     */
    public function insertPendingAccount(...$args)
    {
        return $this->repository->insertPendingAccount(...$args);
    }
    /**
     * Get Next Kyc Application No.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getNextKycApplicationNo(...$args)
    {
        return $this->repository->getNextKycApplicationNo(...$args);
    }
    /**
     * Get Kyc Application.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getKycApplication(...$args)
    {
        return $this->repository->getKycApplication(...$args);
    }
    /**
     * Get User Dashboard Data.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getUserDashboardData(...$args)
    {
        return $this->repository->getUserDashboardData(...$args);
    }
    /**
     * Get Active Account By Customer Id.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getActiveAccountByCustomerId(...$args)
    {
        return $this->repository->getActiveAccountByCustomerId(...$args);
    }
    /**
     * Get Recent Transactions.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getRecentTransactions(...$args)
    {
        return $this->repository->getRecentTransactions(...$args);
    }
    /**
     * Get Transaction To Account Id.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getTransactionToAccountId(...$args)
    {
        return $this->repository->getTransactionToAccountId(...$args);
    }
    /**
     * Get Monthly Income.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getMonthlyIncome(...$args)
    {
        return $this->repository->getMonthlyIncome(...$args);
    }
    /**
     * Get Monthly Expenses.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getMonthlyExpenses(...$args)
    {
        return $this->repository->getMonthlyExpenses(...$args);
    }
    /**
     * Get Unread Notification Count.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getUnreadNotificationCount(...$args)
    {
        return $this->repository->getUnreadNotificationCount(...$args);
    }
    /**
     * Get Past Rejections.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getPastRejections(...$args)
    {
        return $this->repository->getPastRejections(...$args);
    }
    /**
     * Get Notifications Total.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getNotificationsTotal(...$args)
    {
        return $this->repository->getNotificationsTotal(...$args);
    }
    /**
     * Get Notifications.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getNotifications(...$args)
    {
        return $this->repository->getNotifications(...$args);
    }
    /**
     * Mark Notification Read.
     *
     * @param mixed $args
     * @return mixed
     */
    public function markNotificationRead(...$args)
    {
        return $this->repository->markNotificationRead(...$args);
    }
    /**
     * Get Transactions
     */
    public function getTransactions(int $accountId, array $filters): array
    {
        $where_clauses = ["(t.from_account_id = ? OR t.to_account_id = ?)"];
        $params = [$accountId, $accountId];

        if ($filters['type'] !== 'all' && in_array($filters['type'], ['deposit', 'withdrawal', 'transfer', 'interest_credit'])) {
            $where_clauses[] = "t.type = ?";
            $params[] = $filters['type'];
        }
        if ($filters['status'] !== 'all' && in_array($filters['status'], ['completed', 'pending', 'failed', 'reversed'])) {
            $where_clauses[] = "t.status = ?";
            $params[] = $filters['status'];
        }
        if (!\App\Shared\Core\Validation\Validator::isEmpty($filters['from_date'])) {
            $where_clauses[] = "DATE(t.created_at) >= ?";
            $params[] = $filters['from_date'];
        }
        if (!\App\Shared\Core\Validation\Validator::isEmpty($filters['to_date'])) {
            $where_clauses[] = "DATE(t.created_at) <= ?";
            $params[] = $filters['to_date'];
        }
        if (!\App\Shared\Core\Validation\Validator::isEmpty($filters['search'])) {
            $where_clauses[] = "(t.txn_id LIKE ? OR t.description LIKE ? OR t.reference_note LIKE ?)";
            $search_param = "%{$filters['search']}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
        }

        $where_sql = implode(' AND ', $where_clauses);
        $total = (int)$this->repository->getTransactionsCount($where_sql, $params);
        
        $per_page = $filters['per_page'];
        $page = $filters['page'];
        $pages = max(1, ceil($total / $per_page));
        $offset = ($page - 1) * $per_page;
        
        $transactions = $this->repository->getTransactionsList($where_sql, $params, $per_page, $offset);

        foreach ($transactions as &$txn) {
            if (in_array($txn['type'], ['deposit', 'interest_credit'])) {
                $txn['direction'] = 'credit';
            } elseif ($txn['type'] === \App\Domain\Enums\TransactionTypeEnum::WITHDRAWAL->value) {
                $txn['direction'] = 'debit';
            } elseif ($txn['type'] === \App\Domain\Enums\TransactionTypeEnum::TRANSFER->value) {
                $txn['direction'] = ($txn['to_account_id'] == $accountId) ? 'credit' : 'debit';
                if ($txn['to_account_id'] == $accountId && $txn['dest_balance_after'] !== null) {
                    $txn['balance_after'] = $txn['dest_balance_after'];
                }
            }
        }
        unset($txn);

        return [
            'transactions' => $transactions,
            'total' => $total,
            'pages' => $pages,
            'current_page' => $page,
            'per_page' => $per_page
        ];
    }
    /**
     * Mark All Notifications Read.
     *
     * @param mixed $args
     * @return mixed
     */
    public function markAllNotificationsRead(...$args)
    {
        return $this->repository->markAllNotificationsRead(...$args);
    }
    /**
     * Delete Notification.
     *
     * @param mixed $args
     * @return mixed
     */
    public function deleteNotification(...$args)
    {
        return $this->repository->deleteNotification(...$args);
    }
    /**
     * Clear All Notifications.
     *
     * @param mixed $args
     * @return mixed
     */
    public function clearAllNotifications(...$args)
    {
        return $this->repository->clearAllNotifications(...$args);
    }
    /**
     * Get Full Profile.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getFullProfile(...$args)
    {
        return $this->repository->getFullProfile(...$args);
    }
    /**
     * Check Email Exists.
     *
     * @param mixed $args
     * @return mixed
     */
    public function checkEmailExists(...$args)
    {
        return $this->repository->checkEmailExists(...$args);
    }
    /**
     * Check Phone Exists.
     *
     * @param mixed $args
     * @return mixed
     */
    public function checkPhoneExists(...$args)
    {
        return $this->repository->checkPhoneExists(...$args);
    }

    public function checkIdNumberExists(string $idType, string $idNumber, int $excludeUserId = 0): bool
    {
        return $this->repository->checkIdNumberExists($idType, $idNumber, $excludeUserId);
    }
    /**
     * Update Profile.
     *
     * @param mixed $args
     * @return mixed
     */
    public function updateProfile(...$args)
    {
        return $this->repository->updateProfile(...$args);
    }
    /**
     * Get Active Account.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getActiveAccount(...$args)
    {
        return $this->repository->getActiveAccount(...$args);
    }
    /**
     * Get Statement Transactions.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getStatementTransactions(...$args)
    {
        return $this->repository->getStatementTransactions(...$args);
    }
    /**
     * Get Opening Balance.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getOpeningBalance(...$args)
    {
        return $this->repository->getOpeningBalance(...$args);
    }
    /**
     * Get Transactions Count.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getTransactionsCount(...$args)
    {
        return $this->repository->getTransactionsCount(...$args);
    }
    /**
     * Get Transactions List.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getTransactionsList(...$args)
    {
        return $this->repository->getTransactionsList(...$args);
    }
    /**
     * Get Account Id By Number.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getAccountIdByNumber(...$args)
    {
        return $this->repository->getAccountIdByNumber(...$args);
    }
    /**
     * Get Account For Update.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getAccountForUpdate(...$args)
    {
        return $this->repository->getAccountForUpdate(...$args);
    }
    /**
     * Update Account Balance.
     *
     * @param mixed $args
     * @return mixed
     */
    public function updateAccountBalance(...$args)
    {
        return $this->repository->updateAccountBalance(...$args);
    }
    /**
     * Insert Transaction.
     *
     * @param mixed $args
     * @return mixed
     */
    public function insertTransaction(...$args)
    {
        return $this->repository->insertTransaction(...$args);
    }
    /**
     * Check Duplicate Transaction.
     *
     * @param int $fromAccountId
     * @param float $amount
     * @param string $type
     * @return void
     */
    public function checkDuplicateTransaction(int $fromAccountId, float $amount, string $type): void
    {
        $this->repository->checkDuplicateTransaction($fromAccountId, $amount, $type);
    }
    /**
     * Check Daily Transfer Limit.
     *
     * @param int $fromAccountId
     * @param float $amount
     * @param float $dailyLimit
     * @return void
     */
    public function checkDailyTransferLimit(int $fromAccountId, float $amount, float $dailyLimit): void
    {
        $this->repository->checkDailyTransferLimit($fromAccountId, $amount, $dailyLimit);
    }
}
