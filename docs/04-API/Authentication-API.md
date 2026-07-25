---
title: "Authentication API Documentation"
version: "1.0.0"
category: "API Reference"
module: "Authentication"
owner: "Backend Engineering"
last_updated: "2026-07-25"
---

# Authentication API

## Table of Contents
1. [Introduction](#introduction)
2. [Security & Authentication Requirements](#security--authentication-requirements)
3. [API Endpoints](#api-endpoints)
   - [POST /api/v1/auth/login](#post-apiv1authlogin)
   - [POST /api/v1/auth/admin-login](#post-apiv1authadmin-login)
   - [POST /api/v1/auth/staff-login](#post-apiv1authstaff-login)
   - [POST /api/v1/auth/logout](#post-apiv1authlogout)
   - [POST /api/v1/auth/register](#post-apiv1authregister)
   - [GET /api/v1/auth/csrf-token](#get-apiv1authcsrf-token)
4. [Error Codes](#error-codes)

## Introduction
The `Authentication` module manages user authentication, registration, session lifecycle, and step-up MFA logic for the Finzo Banking System.

## Security & Authentication Requirements
- **CSRF Enforcement:** ALL POST endpoints in this module require a valid `X-CSRF-TOKEN` header. Retrieve this token via `GET /api/v1/auth/csrf-token`.
- **Session Handling:** The system currently relies on strict HTTP-only cookies (`PHPSESSID`) managed by `SessionManager.php`.
- **Future JWT Readiness:** The API is instrumented to accept `Bearer` tokens in the `Authorization` header for future stateless authentication (Phase 18+).

---

## API Endpoints

### POST `/api/v1/auth/login`
Authenticates a standard Customer account and establishes an HTTP-only secure session.

**Middleware / Guards:** `CsrfMiddleware`, `GuestMiddleware` (must not be logged in).

#### Request Body (JSON)
```json
{
    "email": "customer@finzo.com",
    "password": "SecurePassword123!"
}
```

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Login successful",
    "timestamp": "2026-07-25T00:00:00Z",
    "data": {
        "user_id": 1,
        "email": "customer@finzo.com",
        "role": "customer",
        "mfa_required": false
    }
}
```

---

### POST `/api/v1/auth/admin-login`
Authenticates an Administrative account. Employs stricter rate-limiting and authorization checks.

**Middleware / Guards:** `CsrfMiddleware`, `GuestMiddleware`.

#### Request Body (JSON)
```json
{
    "email": "admin@finzo.com",
    "password": "AdminPassword123!"
}
```

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Admin login successful",
    "timestamp": "2026-07-25T00:00:00Z",
    "data": {
        "admin_id": 1,
        "email": "admin@finzo.com",
        "role": "super_admin",
        "mfa_required": true
    }
}
```
*(Note: If `mfa_required` is true, the session is placed in a pending state until MFA is verified).*

---

### POST `/api/v1/auth/staff-login`
Authenticates a Staff member (e.g., Manager, Teller).

**Middleware / Guards:** `CsrfMiddleware`, `GuestMiddleware`.

#### Request Body (JSON)
```json
{
    "email": "staff@finzo.com",
    "password": "StaffPassword123!"
}
```

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Staff login successful",
    "data": {
        "staff_id": 1,
        "role": "manager"
    }
}
```

---

### POST `/api/v1/auth/logout`
Destroys the active session, clears authentication cookies, and invalidates the CSRF token.

**Middleware / Guards:** `CsrfMiddleware`.

#### Request Body
*Empty body required. Must include CSRF token header.*

#### Response Example (200 OK)
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

### POST `/api/v1/auth/register`
Registers a new customer account.

**Middleware / Guards:** `CsrfMiddleware`, `GuestMiddleware`.

#### Request Body (JSON)
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "johndoe@example.com",
    "phone": "+1234567890",
    "password": "StrongPassword123!",
    "password_confirmation": "StrongPassword123!"
}
```

#### Response Example (201 Created)
```json
{
    "success": true,
    "message": "Registration successful. Please verify your email.",
    "data": {
        "user_id": 12
    }
}
```

---

### GET `/api/v1/auth/csrf-token`
Retrieves a valid CSRF token. Must be called before initiating any POST authentication requests.

#### Response Example (200 OK)
```json
{
    "success": true,
    "csrf_token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6"
}
```

## Error Codes
- `400 Bad Request`: Missing required fields (e.g., email or password).
- `401 Unauthorized`: Invalid credentials, invalid CSRF token, or account locked.
- `403 Forbidden`: Account deactivated or lacking necessary permissions.
- `404 Not Found`: Endpoint missing.
- `422 Unprocessable Entity`: Data validation failure (e.g., weak password).
- `429 Too Many Requests`: Account lockout triggered due to brute-force attempts.
