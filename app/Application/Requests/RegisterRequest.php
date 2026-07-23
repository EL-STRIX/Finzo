<?php
namespace App\Application\Requests;

class RegisterRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'full_name' => 'required|min:2',
            'phone' => 'required|numeric'
        ];
    }
}