---
title: "Session Management"
category: "Security"
module: "Session"
last_updated: "2026-07-25"
---

# Session Management

## 1. Overview
The Finzo Banking System controls all PHP session operations through the `App\Shared\Security\SessionManager` class. This abstracts raw `$_SESSION` hydration away from controllers and ensures that standard enterprise-grade cookie rules are globally enforced.

## 2. Session Initialization & Fixation Prevention
When `AuthController` completes credential validation via `AuthService`, it passes the payload to `SessionManager`.

Before a user is considered logged in, `SessionManager` ALWAYS calls:
```php
session_regenerate_id(true);
```
This strictly mitigates Session Fixation attacks by destroying the old unauthenticated session identifier and provisioning a completely new one before assigning privileges.

## 3. Strict Cookie Configuration
To prevent hijacking, MITM attacks, and XSS extraction, the `SessionManager` enforces these configurations via `session_set_cookie_params()`:
- `HttpOnly`: Strictly set to `true`. JavaScript running in the browser cannot read the `PHPSESSID` cookie.
- `Secure`: Set to `true` dynamically if `$_SERVER['HTTPS']` is active.
- `SameSite`: Set to `Strict`. The cookie is absolutely never sent on cross-site requests, acting as a secondary line of defense against CSRF.

## 4. Absolute & Idle Timeouts
While not fully driven by Redis/memcached in this phase, session limits are tracked logically:
- **Idle Timeout**: Regulated via middleware. If `last_activity` exceeds 15 minutes, the session is invalidated.
- **Absolute Timeout**: Maximum allowed lifespan of a cookie before re-authentication is forcefully required, regardless of activity.

## 5. Session Destruction
During logout, the system does not simply clear the `$_SESSION` array. It performs a complete purge:
1. Empties the `$_SESSION` array.
2. Destroys the cookie via `setcookie()` with a negative expiration time.
3. Calls `session_destroy()`.
