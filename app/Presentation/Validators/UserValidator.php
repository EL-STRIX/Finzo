<?php
namespace App\Presentation\Validators;

use App\Domain\Exceptions\ValidationException;
use App\Application\Services\UserService;

class UserValidator
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function validateProfileUpdate(array $data, int $userId, string $currentEmail): array
    {
        $errors = [];
        $fullName = $data['full_name'] ?? '';
        $phone = $data['phone'] ?? '';
        $email = $data['email'] ?? '';

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
            throw new ValidationException(json_encode($errors));
        }

        return [
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email
        ];
    }
}
