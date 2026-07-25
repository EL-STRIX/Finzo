---
title: "Authentication Architecture"
category: "Security"
module: "Authentication"
last_updated: "2026-07-25"
---

# Authentication Architecture

## 1. Overview
The Finzo Banking System utilizes a highly decoupled, service-oriented authentication architecture. Authentication workflows (Login, Registration, Password Resets) are managed entirely by the `AuthService`, which performs cryptographic validation and domain rules enforcement, while the `AuthController` orchestrates the HTTP boundaries.

## 2. Component Decoupling (Phase 17.4)
To ensure future-readiness (JWT and MFA support), the system strict enforces a separation between authentication state and HTTP session hydration.

- **`AuthService.php`**: Validates credentials using `BCrypt`. It never touches the `$_SESSION` superglobal. Instead, it returns a strictly typed `array` containing the user's data and an `mfa_required` flag.
- **`AuthController.php`**: Receives the payload from `AuthService`. If `mfa_required` is false, it hands off the hydration responsibility to `SessionManager.php`.
- **`SessionManager.php`**: Safely initializes the PHP session, regenerates the session ID, and issues strict HttpOnly cookies.

## 3. Supported Authentication Workflows
1. **Customer Login**: Base authentication against the `users` table.
2. **Staff Login**: Authenticates branch personnel against the `staff` table.
3. **Admin Login**: Authenticates high-privilege system administrators against the `admins` table. Employs stricter rate limiting.

## 4. Multi-Factor Authentication (MFA) Readiness
The domain layer returns an `mfa_required` boolean flag during the authentication flow. Controllers check this flag before issuing a session:
```php
$result = $this->authService->login($email, $password);
if ($result['mfa_required']) {
    // Return 202 Accepted - Client must fulfill MFA challenge
}
```

## 5. Security Measures
- **Rate Limiting**: `RateLimiter::throttle()` is invoked on all failed login attempts.
- **Account Lockouts**: `UserService::incrementFailedLogins()` tracks failures and permanently locks accounts after threshold breach.
- **Password Hashing**: Strictly BCrypt (Cost: 12).
- **CSRF**: `CsrfMiddleware` explicitly guards all POST authentication requests.
