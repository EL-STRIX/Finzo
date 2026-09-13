<?php

namespace App\Presentation\Validators;

use App\Shared\Core\Validation\Validator;
use App\Shared\Core\Exceptions\ValidationException;
use App\Application\Services\UserService;

class UserValidator extends Validator
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function validateProfileUpdate(array $data, int $userId, string $currentEmail): array
    {
        $errors = [];
        $fullName = self::sanitize(['full_name' => $data['full_name'] ?? ''])['full_name'];
        $phone    = self::sanitize(['phone' => $data['phone'] ?? ''])['phone'];
        $email    = self::sanitize(['email' => $data['email'] ?? ''])['email'];

        if (empty($fullName) || strlen($fullName) < 2 || strlen($fullName) > 100) {
            $errors['full_name'] = 'Full name must be between 2 and 100 characters';
        }
        if (!empty($phone) && !validate_phone($phone)) {
            $errors['phone'] = 'Please enter a valid 10-digit Indian mobile number';
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        if (!empty($email) && $email !== $currentEmail && $this->userService->checkEmailExists($email, $userId)) {
            $errors['email'] = 'This email is already in use by another account';
        }
        if (!empty($phone) && $this->userService->checkPhoneExists($phone, $userId)) {
            $errors['phone'] = 'This phone number is already in use by another account';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }

        return [
            'full_name' => $fullName,
            'phone'     => $phone,
            'email'     => $email
        ];
    }

    public function validatePasswordUpdate(array $data): array
    {
        $errors = [];
        $currentPassword = $data['current_password'] ?? '';
        $newPassword     = $data['new_password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        if (empty($currentPassword)) {
            $errors['current_password'] = 'Current password is required';
        }
        if (empty($newPassword)) {
            $errors['new_password'] = 'New password is required';
        } else {
            $strength = validate_password_strength($newPassword);
            if (!$strength['valid']) {
                $errors['new_password'] = $strength['message'];
            }
        }
        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        if ($currentPassword === $newPassword && !empty($newPassword)) {
            $errors['new_password'] = 'New password must be different from current password';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }

        return [
            'current_password' => $currentPassword,
            'new_password'     => $newPassword
        ];
    }

    public function validatePinChange(array $data): array
    {
        $errors = [];
        $authType = trim($data['auth_type'] ?? 'pin');
        if (!in_array($authType, ['pin', 'password'], true)) {
            $authType = 'pin';
        }

        $currentPin = trim($data['current_pin'] ?? '');
        $currentPassword = $data['current_password'] ?? '';
        $newPin = trim($data['new_pin'] ?? '');
        $confirmPin = trim($data['confirm_pin'] ?? '');

        if ($authType === 'password') {
            if (empty($currentPassword)) {
                $errors['current_password'] = 'Account password is required';
            }
        } else {
            if (empty($currentPin)) {
                $errors['current_pin'] = 'Current 4-digit PIN is required';
            } elseif (!preg_match('/^\d{4}$/', $currentPin)) {
                $errors['current_pin'] = 'Current PIN must be exactly 4 digits';
            }
        }

        if (empty($newPin)) {
            $errors['new_pin'] = 'New 4-digit PIN is required';
        } elseif (!preg_match('/^\d{4}$/', $newPin)) {
            $errors['new_pin'] = 'New PIN must be exactly 4 numeric digits';
        }

        if (empty($confirmPin)) {
            $errors['confirm_pin'] = 'Please confirm your new PIN';
        } elseif ($newPin !== $confirmPin) {
            $errors['confirm_pin'] = 'New PINs do not match';
        }

        if ($authType === 'pin' && !empty($newPin) && $currentPin === $newPin) {
            $errors['new_pin'] = 'New PIN must be different from current PIN';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }

        return [
            'auth_type'        => $authType,
            'current_pin'      => $currentPin,
            'current_password' => $currentPassword,
            'new_pin'          => $newPin,
            'confirm_pin'      => $confirmPin,
        ];
    }

    public function validateBeneficiary(array $data): array
    {
        $errors = [];
        $nickname = self::sanitize(['nickname' => $data['nickname'] ?? ''])['nickname'];
        $accountNo = self::sanitize(['account_no' => $data['account_no'] ?? ''])['account_no'];

        if (empty($nickname) || strlen($nickname) < 2 || strlen($nickname) > 100) {
            $errors['nickname'] = 'Nickname must be between 2 and 100 characters';
        }
        if (empty($accountNo)) {
            $errors['account_no'] = 'Account number is required';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }

        return [
            'nickname'   => $nickname,
            'account_no' => $accountNo
        ];
    }

    public function validateTransfer(array $data): array
    {
        $errors = [];
        $rawAccount  = $data['to_account_no'] ?? $data['to_account'] ?? '';
        $toAccountNo = strtoupper(trim(self::sanitize(['to_account_no' => $rawAccount])['to_account_no']));
        $amount      = floatval($data['amount'] ?? 0);
        $description = self::sanitize(['description' => $data['description'] ?? ''])['description'];
        $txnPin      = trim(self::sanitize(['txn_pin' => $data['txn_pin'] ?? $data['pin'] ?? ''])['txn_pin']);

        if (empty($toAccountNo)) {
            $errors['to_account_no'] = 'Recipient account number is required';
        }
        if ($amount <= 0) {
            $errors['amount'] = 'Amount must be greater than zero';
        }
        if (floor($amount * 100) != $amount * 100) {
            $errors['amount'] = 'Amount can have at most 2 decimal places';
        }
        if (strlen($description) > 100) {
            $errors['description'] = 'Description must not exceed 100 characters';
        }
        if (empty($txnPin)) {
            $errors['txn_pin'] = 'Please enter your 4-digit transaction PIN';
        } elseif (!preg_match('/^\d{4}$/', $txnPin)) {
            $errors['txn_pin'] = 'Transaction PIN must be exactly 4 numeric digits';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }

        return [
            'to_account_no' => $toAccountNo,
            'amount'        => $amount,
            'description'   => $description,
            'txn_pin'       => $txnPin
        ];
    }
}
