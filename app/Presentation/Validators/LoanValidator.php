<?php

namespace App\Presentation\Validators;

use App\Shared\Core\Validation\Validator;

class LoanValidator extends Validator
{
    public function validateProductCreation(array $data): array
    {
        $errors = [];
        $sanitized = self::sanitize($data);

        if (empty($sanitized['name'])) {
            $errors['name'] = 'Product name is required';
        }
        if (empty($sanitized['interest_rate']) || !is_numeric($sanitized['interest_rate']) || $sanitized['interest_rate'] <= 0) {
            $errors['interest_rate'] = 'Valid interest rate is required';
        }
        if (empty($sanitized['min_amount']) || !is_numeric($sanitized['min_amount']) || $sanitized['min_amount'] <= 0) {
            $errors['min_amount'] = 'Valid minimum amount is required';
        }
        if (empty($sanitized['max_amount']) || !is_numeric($sanitized['max_amount']) || $sanitized['max_amount'] <= $sanitized['min_amount']) {
            $errors['max_amount'] = 'Valid maximum amount is required';
        }
        if (empty($sanitized['min_tenure_months']) || !is_numeric($sanitized['min_tenure_months']) || $sanitized['min_tenure_months'] <= 0) {
            $errors['min_tenure_months'] = 'Valid minimum tenure is required';
        }
        if (empty($sanitized['max_tenure_months']) || !is_numeric($sanitized['max_tenure_months']) || $sanitized['max_tenure_months'] <= $sanitized['min_tenure_months']) {
            $errors['max_tenure_months'] = 'Valid maximum tenure is required';
        }

        if (!empty($errors)) {
            throw new \App\Shared\Core\Exceptions\ValidationException('Validation failed', ['errors' => $errors]);
        }

        return $sanitized;
    }

    public function validateLoanApplication(array $data, array $product): array
    {
        $errors = [];
        $sanitized = self::sanitize($data);

        if (empty($sanitized['amount']) || !is_numeric($sanitized['amount'])) {
            $errors['amount'] = 'Valid loan amount is required';
        } elseif ($sanitized['amount'] < $product['min_amount'] || $sanitized['amount'] > $product['max_amount']) {
            $errors['amount'] = "Amount must be between {$product['min_amount']} and {$product['max_amount']}";
        }

        if (empty($sanitized['tenure_months']) || !is_numeric($sanitized['tenure_months'])) {
            $errors['tenure_months'] = 'Valid tenure is required';
        } elseif ($sanitized['tenure_months'] < $product['min_tenure_months'] || $sanitized['tenure_months'] > $product['max_tenure_months']) {
            $errors['tenure_months'] = "Tenure must be between {$product['min_tenure_months']} and {$product['max_tenure_months']} months";
        }

        if (empty($sanitized['purpose'])) {
            $errors['purpose'] = 'Loan purpose is required';
        }

        if (!empty($errors)) {
            throw new \App\Shared\Core\Exceptions\ValidationException('Validation failed', ['errors' => $errors]);
        }

        return $sanitized;
    }
}
