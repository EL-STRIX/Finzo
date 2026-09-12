<?php
namespace App\Application\Requests;

class RegisterRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
            'full_name' => 'required|min:2|max:100',
            'phone' => 'required',
            'confirm_password' => 'required'
        ];
    }

    protected function validate(): void
    {
        parent::validate();

        $normalizedPhone = normalize_phone($this->get('phone'));
        $this->data['phone'] = $normalizedPhone;

        if (!validate_phone($normalizedPhone)) {
            $errors['phone'] = 'Please enter a valid 10-digit Indian mobile number';
        }

        $strength = validate_password_strength($this->get('password'));
        if (!$strength['valid']) {
            $errors['password'] = $strength['message'];
        }

        if ($this->get('password') !== $this->get('confirm_password')) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            throw new \App\Shared\Core\Exceptions\ValidationException('Validation failed', ['errors' => $errors]);
        }
    }
}