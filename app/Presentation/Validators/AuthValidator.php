<?php

namespace App\Presentation\Validators;

use App\Shared\Core\Exceptions\ApiException;

class AuthValidator
{
    public static function validateLogin(string $identity, string $password, string $csrfToken): void
    {
        if (empty($csrfToken)) {
            throw new ApiException('Security token missing. Please refresh the page.', 403);
        }
        if (!validate_csrf_token($csrfToken)) {
            throw new ApiException('Invalid or expired security session. Please try again.', 403);
        }
        if (empty($identity) || empty($password)) {
            throw new ApiException('Identity and password are required', 422);
        }
    }
}
