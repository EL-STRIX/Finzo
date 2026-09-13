<?php

namespace App\Presentation\Validators;

use App\Shared\Core\Validation\Validator;
use App\Shared\Core\Exceptions\ValidationException;

class StaffValidator extends Validator
{
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
            $errors['confirm_password'] = 'Please confirm your new password';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }

        return [
            'current_password' => $currentPassword,
            'new_password'     => $newPassword
        ];
    }

    public function validateDeposit(array $data): array
    {
        $errors = [];
        $accountNumber = self::sanitize(['account_no' => $data['account_no'] ?? ''])['account_no'];
        $amount        = floatval($data['amount'] ?? 0);
        $referenceNote = self::sanitize(['reference_note' => $data['reference_note'] ?? ''])['reference_note'];

        if (empty($accountNumber)) {
            $errors['account_no'] = 'Account number is required';
        }
        if ($amount < 100) {
            $errors['amount'] = 'Minimum deposit amount is ₹100';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }

        return [
            'account_no'     => $accountNumber,
            'amount'         => $amount,
            'reference_note' => $referenceNote
        ];
    }

    public function validateWithdrawal(array $data): array
    {
        $errors = [];
        $accountNumber = self::sanitize(['account_no' => $data['account_no'] ?? ''])['account_no'];
        $amount        = floatval($data['amount'] ?? 0);
        $identityNotes = self::sanitize(['identity_notes' => $data['identity_notes'] ?? ''])['identity_notes'];

        if (empty($accountNumber)) {
            $errors['account_no'] = 'Account number is required';
        }
        if ($amount <= 0) {
            $errors['amount'] = 'Amount must be greater than zero';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }

        return [
            'account_no'     => $accountNumber,
            'amount'         => $amount,
            'identity_notes' => $identityNotes
        ];
    }

    public function validateProfileUpdate(array $data, ?array $file): array
    {
        $errors = [];
        $phone                   = self::sanitize(['phone' => $data['phone'] ?? ''])['phone'];
        $emergency_contact_name  = self::sanitize(['emergency_contact_name' => $data['emergency_contact_name'] ?? ''])['emergency_contact_name'];
        $emergency_contact_phone = self::sanitize(['emergency_contact_phone' => $data['emergency_contact_phone'] ?? ''])['emergency_contact_phone'];

        $street                  = self::sanitize(['street' => $data['street'] ?? ''])['street'];
        $city                    = self::sanitize(['city' => $data['city'] ?? ''])['city'];
        $state                   = self::sanitize(['state' => $data['state'] ?? ''])['state'];
        $pincode                 = self::sanitize(['pincode' => $data['pincode'] ?? ''])['pincode'];
        $country                 = self::sanitize(['country' => $data['country'] ?? ''])['country'];

        if ($phone !== '' && !validate_phone($phone)) {
            $errors['phone'] = 'Enter a valid 10-digit mobile number';
        }
        if ($emergency_contact_phone !== '' && !validate_phone($emergency_contact_phone)) {
            $errors['emergency_contact_phone'] = 'Enter a valid 10-digit emergency contact number';
        }
        if ($pincode !== '' && !preg_match('/^[0-9]{6}$/', $pincode)) {
            $errors['pincode'] = 'Enter a valid 6-digit pincode';
        }

        if (is_array($file) && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            if (!in_array($mimeType, ALLOWED_IMAGE_TYPES ?? ['image/jpeg', 'image/png'])) {
                $errors['profile_photo'] = 'Only JPEG and PNG images are allowed';
            }
            if ($file['size'] > (MAX_UPLOAD_SIZE ?? 5242880)) {
                $errors['profile_photo'] = 'Profile photo must be under 5 MB';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }

        return [
            'phone'                   => $phone,
            'emergency_contact_name'  => $emergency_contact_name,
            'emergency_contact_phone' => $emergency_contact_phone,
            'street'                  => $street,
            'city'                    => $city,
            'state'                   => $state,
            'pincode'                 => $pincode,
            'country'                 => $country
        ];
    }
}
