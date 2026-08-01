<?php
namespace App\Application\Requests;

class LoginRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'identity' => 'required',
            'password' => 'required|min:6'
        ];
    }
}