<?php

namespace App\Application\Actions;

use App\Domain\Contracts\Services\UserServiceInterface;
use App\Shared\Security\SecurityServiceInterface;
use Exception;
use PDOException;

class CompleteUserProfileAction
{
    private UserServiceInterface $userService;
    private SecurityServiceInterface $security;

    public function __construct(UserServiceInterface $userService, SecurityServiceInterface $security)
    {
        $this->userService = $userService;
        $this->security = $security;
    }

    public function execute(\App\Application\DTOs\KycRequestDTO $dto): void
    {
        $userId = $dto->userId;
        $postData = $dto->postData;
        $files = $dto->filesData;
        $customer_id_str = $this->userService->getCustomerId($userId);

        $data = [
            'user_id' => $userId,
            'dob' => $this->security->cleanInput($postData['dob'] ?? ''),
            'gender' => $this->security->cleanInput($postData['gender'] ?? ''),
            'father_name' => $this->security->cleanInput($postData['father_name'] ?? $postData['father-name'] ?? ''),
            'mother_name' => $this->security->cleanInput($postData['mother_name'] ?? $postData['mother-name'] ?? ''),
            'marital_status' => $this->security->cleanInput($postData['marital_status'] ?? $postData['marital-status'] ?? ''),
            'nationality' => $this->security->cleanInput($postData['nationality'] ?? ''),
            'occupation' => $this->security->cleanInput($postData['occupation'] ?? ''),
            'annual_income' => $this->security->cleanInput($postData['annual_income'] ?? $postData['income'] ?? ''),
            'source_of_income' => $this->security->cleanInput($postData['source_of_income'] ?? $postData['source-of-income'] ?? ''),
            'street' => $this->security->cleanInput($postData['street'] ?? ''),
            'city' => $this->security->cleanInput($postData['city'] ?? ''),
            'state' => $this->security->cleanInput($postData['state'] ?? ''),
            'pincode' => $this->security->cleanInput($postData['pincode'] ?? ''),
            'id_type' => $this->security->cleanInput($postData['id_type'] ?? $postData['id-type'] ?? ''),
            'id_number' => $this->security->cleanInput($postData['id_number'] ?? $postData['id-number'] ?? ''),
            'account_type' => $this->security->cleanInput($postData['account_type'] ?? $postData['account-type'] ?? 'savings'),
            'initial_deposit' => floatval($postData['initial_deposit'] ?? 0),
            'txn_pin' => $postData['txn_pin'] ?? '1234',
        ];

        if (isset($files['profile_photo']) && !isset($files['profile_picture'])) {
            $files['profile_picture'] = $files['profile_photo'];
        }

        $existing_user = $this->userService->getUserFiles($userId);
        $data['has_profile_pic'] = !\App\Shared\Core\Validation\Validator::isEmpty($existing_user['profile_picture']);
        $data['has_id_front'] = !\App\Shared\Core\Validation\Validator::isEmpty($existing_user['id_front_path']);
        $data['has_id_back'] = !\App\Shared\Core\Validation\Validator::isEmpty($existing_user['id_back_path']);

        $errors = $this->userService->validateKyc($data, $files);
        if (!\App\Shared\Core\Validation\Validator::isEmpty($errors)) {
            throw new \App\Shared\Core\Exceptions\ApiException('Validation failed', 422, $errors);
        }

        // Encrypt highly sensitive PII before saving to the database
        if (!empty($data['id_number'])) {
            $crypto = new \App\Shared\Security\EncryptionService();
            $data['id_number'] = $crypto->encryptSearchable($data['id_number']);
        }

        try {
            $paths = $this->userService->processKycUploads($files, $userId, $existing_user);
        } catch (Exception $e) {
            throw new \App\Shared\Core\Exceptions\ApiException($e->getMessage(), 500);
        }

        try {
            $pdo = $this->userService->getDb();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_ef776789'));
            $stmt->execute([$paths['profile_pic_path'], $data['dob'], $data['gender'], $data['father_name'], $data['mother_name'], $data['marital_status'], $data['nationality'], $data['occupation'], $data['annual_income'], $data['source_of_income'], $data['street'], $data['city'], $data['state'], $data['pincode'], $data['id_type'], $data['id_number'], $paths['id_front_path'], $paths['id_back_path'], $userId]);

            $txn_pin_hash = password_hash($data['txn_pin'], PASSWORD_BCRYPT);
            $existing_account = $this->userService->getExistingAccountByCustomerId($customer_id_str);

            if ($existing_account) {
                $this->userService->updateAccountResubmission($data['account_type'], $data['initial_deposit'], $txn_pin_hash, $customer_id_str);
            } else {
                $this->userService->insertPendingAccount($customer_id_str, 'PENDING_' . $userId, $data['account_type'], $data['initial_deposit'], $txn_pin_hash);
            }

            $year = date('Y');
            $next_num = $this->userService->getNextKycApplicationNo($year) + 1;
            $application_no = 'KYC' . $year . str_pad($next_num, 7, '0', STR_PAD_LEFT);
            $existing_app = $this->userService->getKycApplication($customer_id_str);

            if ($existing_app) {
                $new_count = $existing_app['submission_count'] + 1;
                $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_371058c0'));
                $stmt->execute([$new_count, $data['dob'], $data['gender'], $data['father_name'], $data['occupation'], $data['annual_income'], $data['street'], $data['city'], $data['state'], $data['pincode'], $data['id_type'], $data['id_number'], $paths['id_front_path'], $paths['id_back_path'], $data['account_type'], $data['initial_deposit'], $customer_id_str]);
            } else {
                $stmt = $pdo->prepare(\App\Infrastructure\Repositories\QueryStore::get('q_e07f7e3c'));
                $stmt->execute([$customer_id_str, $application_no, $data['dob'], $data['gender'], $data['father_name'], $data['occupation'], $data['annual_income'], $data['street'], $data['city'], $data['state'], $data['pincode'], $data['id_type'], $data['id_number'], $paths['id_front_path'], $paths['id_back_path'], $data['account_type'], $data['initial_deposit']]);
            }
            $pdo->commit();
            
            $_SESSION['user_status'] = 'under_review';
            require_once FINZO_ROOT . '/config/mailer.php';
            send_under_review_email($_SESSION['user_email'], $_SESSION['user_name']);
            send_notification($pdo, $customer_id_str, \App\Domain\Enums\NotificationTypeEnum::SYSTEM->value, 'Application Submitted for Review', 'Your KYC profile has been successfully submitted. Our team will review your documents and notify you within 24 hours.');
            
        } catch (PDOException $e) {
            if (\App\Shared\Core\Validation\Validator::isSet($pdo)) {
                $pdo->rollBack();
            }
            if (\App\Shared\Core\Validation\Validator::isSet($paths['front_destination']) && file_exists($paths['front_destination'])) {
                unlink($paths['front_destination']);
            }
            if (\App\Shared\Core\Validation\Validator::isSet($paths['back_destination']) && file_exists($paths['back_destination'])) {
                unlink($paths['back_destination']);
            }
            \App\Shared\Core\Logging\Logger::getInstance()->error('KYC DB Error: ', ['context' => $e->getMessage()]);
            throw new \App\Shared\Core\Exceptions\ApiException('An error occurred while processing your application. Please try again.', 500);
        }
    }
}
