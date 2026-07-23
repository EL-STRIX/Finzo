<?php
namespace App\Application\Requests;

class LoginRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ];
    }
}