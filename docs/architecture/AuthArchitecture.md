# Finzo Authentication Architecture

This document describes the modern, decoupled authentication architecture implemented in the Finzo Banking Management System.

## Architecture Overview

Finzo's authentication subsystem is designed to support Session-based authentication, Multi-Factor Authentication (MFA), and JSON Web Token (JWT) Bearer authentication natively, without heavy dependencies.

### Key Components

- **`AuthService.php`**: The business layer responsible for validating user credentials against the database. It enforces account locking, suspension checks, and logs successful/failed login attempts. Crucially, **this service does not manipulate `$_SESSION` directly**. It returns an MFA-aware payload to the controller.
- **`AuthController.php`**: The presentation layer that handles HTTP interactions. It receives the result from `AuthService`. If MFA is required, it returns a challenge redirect. If authentication is complete, it calls `SessionManager` to securely establish the session.
- **`SessionManager.php`**: The secure session handler. It enforces strict session policies (HttpOnly, Secure, SameSite) and hydrates `$_SESSION` appropriately for Users, Admins, and Staff.
- **`Auth.php`**: The global authentication checker (`Auth::requireAuth()`). It acts as a bridge for future JWT API support by first attempting to resolve a `Bearer` token via the `resolveBearerToken()` hook, before falling back to `$_SESSION`.

## MFA Integration Strategy (Future)

The architecture is fully primed for MFA (TOTP, Email OTP, SMS). To activate MFA in the future:
1. Ensure the user table has `mfa_enabled` and `mfa_secret` columns.
2. In `AuthService::loginUser`, if `mfa_enabled === true`, return `['mfa_required' => true, 'mfa_token' => <temporary_auth_token>]`.
3. The `AuthController` will automatically redirect the user to `/views/auth/mfa-challenge.html`.
4. Create an `mfaVerify()` method in the controller to validate the 2FA code and subsequently call `SessionManager::establishUserSession()`.

## JWT / API Authentication Strategy (Future)

To fully implement stateless API authentication:
1. Update `Auth::resolveBearerToken()` to decode the JWT and return a standard user array: `['id' => 1, 'type' => 'customer', 'role' => 'customer', 'is_token' => true]`.
2. The `is_token => true` flag automatically bypasses session timeout logic in `Auth::requireAuth()`.
3. Create a `TokenService` to handle token issuance (Access Tokens, Refresh Tokens) and blacklisting.

## Security Considerations
- All session regeneration is handled natively by `SessionManager` upon login to prevent Session Fixation.
- The `AuthService` dispatches security audit events for all login attempts and lockouts.
- Controllers strictly sanitize inputs before passing them to the authentication domain layer.
