<?php

namespace App\Presentation\Validators;

use App\Shared\Core\Exceptions\ApiException;
use App\Shared\Core\Validation\Validator;

class AuthValidator
{
    public static function validateLogin(string $identity, string $password, string $csrfToken): void
    {
        if (empty($identity) || empty($password)) {
            throw new ApiException('Identity and password are required', 422);
        }
    }

    public static function validateCustomerLogin(string $identity, string $password, string $csrfToken, \App\Shared\Security\SecurityService $security): void
    {
        if (Validator::isEmpty($identity) || Validator::isEmpty($password)) {
            throw new ApiException('Email/Account Number and password are required', 422);
        }
    }

    public static function validateForgotPassword(string $email, string $csrfToken, \App\Shared\Security\SecurityService $security): void
    {

        if (Validator::isEmpty($email) || !Validator::filterVar($email, FILTER_VALIDATE_EMAIL)) {
            throw new ApiException('Please enter a valid email address', 422);
        }
    }

    public static function validateResetPassword(string $token, string $newPassword, string $confirmPassword, string $csrfToken, \App\Shared\Security\SecurityService $security): void
    {

        $errors = [];

        if (Validator::isEmpty($token)) {
            throw new ApiException('Reset token is required', 422);
        }

        if (Validator::isEmpty($newPassword)) {
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

        if (!Validator::isEmpty($errors)) {
            throw new ApiException('Validation failed', 422, ['errors' => $errors]);
        }
    }

    public static function validateRegistration(string $fullName, string $email, string $phone, string $password, string $confirmPassword, string $csrfToken, \App\Shared\Security\SecurityService $security): void
    {

        $errors = [];

        if (Validator::isEmpty($fullName)) {
            $errors['full_name'] = 'Full name is required';
        } elseif (Validator::length($fullName) < 2 || Validator::length($fullName) > 100) {
            $errors['full_name'] = 'Full name must be between 2 and 100 characters';
        }

        if (Validator::isEmpty($email)) {
            $errors['email'] = 'Email address is required';
        } elseif (!Validator::filterVar($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        if (Validator::isEmpty($phone)) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!validate_phone($phone)) {
            $errors['phone'] = 'Please enter a valid 10-digit Indian mobile number';
        }

        if (Validator::isEmpty($password)) {
            $errors['password'] = 'Password is required';
        } else {
            $strength = validate_password_strength($password);
            if (!$strength['valid']) {
                $errors['password'] = $strength['message'];
            }
        }

        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (!Validator::isEmpty($errors)) {
            throw new ApiException('Validation failed', 422, ['errors' => $errors]);
        }
    }
}
