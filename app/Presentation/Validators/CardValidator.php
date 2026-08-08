<?php

namespace App\Presentation\Validators;

use App\Shared\Core\Exceptions\ValidationException;
use App\Shared\Core\Validation\Validator;

class CardValidator
{
    private Validator $validator;

    public function __construct()
    {
        $this->validator = new Validator();
    }

    public function validateProductCreation(array $data): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:debit,credit,virtual'],
            'network' => ['required', 'string', 'in:visa,mastercard,rupay'],
            'daily_withdrawal_limit' => ['required', 'numeric', 'min:0'],
            'daily_purchase_limit' => ['required', 'numeric', 'min:0'],
        ];

        if (!$this->validator->validate($data, $rules)) {
            throw new ValidationException('Invalid card product data', $this->validator->getErrors());
        }
    }

    public function validateCardRequest(array $data): void
    {
        $rules = [
            'card_product_id' => ['required', 'numeric', 'min:1'],
        ];

        if (!$this->validator->validate($data, $rules)) {
            throw new ValidationException('Invalid card request data', $this->validator->getErrors());
        }
    }

    public function validatePin(array $data): void
    {
        $rules = [
            'pin' => ['required', 'string', 'min:4', 'max:4'], // Assuming 4-digit PIN
        ];

        if (!$this->validator->validate($data, $rules)) {
            throw new ValidationException('Invalid PIN format', $this->validator->getErrors());
        }
    }
}
