---
title: "Security Testing Guidelines"
version: "1.0.0"
status: "Active"
owner: "Security & QA Architecture Teams"
category: "Specialized Testing"
last_updated: "2026-07-24"
---

# Security Testing Guidelines

> This document defines the methodologies and automated testing requirements used to verify the defensive boundaries of the Finzo platform. 
> *For architectural security policies (e.g., encryption standards), refer to the primary [Security Architecture](../05-Security/README.md).*

## 1. Purpose and Scope

Finzo handles highly sensitive financial and personal data (PII). Security testing is not optional; it is a mandatory gate in the CI pipeline.
The scope of security testing involves verifying that malicious input, unauthorized access attempts, and session hijack vectors are actively blocked by our middleware and validation layers.

## 2. Security Testing Objectives

*   **Zero Escalation:** Ensure Customers can never access Staff endpoints, and Staff can never access Admin endpoints.
*   **Data Isolation:** Ensure a Customer can only view or mutate their own banking records.
*   **Payload Neutralization:** Guarantee that all JSON and Form payloads are sanitized to prevent SQLi and XSS.

## 3. Core Verification Areas

### 3.1 Authentication & Session Testing
*   **Brute Force:** Test that `AuthenticationService` locks accounts after 5 failed login attempts.
*   **Session Integrity:** Assert that the `PHPSESSID` regenerates upon login and logout to prevent session fixation.
*   **Token Expiration:** Assert that CSRF tokens and Password Reset hashes expire according to system configuration.

### 3.2 Authorization & RBAC Verification
Automated Feature tests must explicitly attempt to breach route boundaries.
*   *Test:* Instantiate a `Customer` user and issue a `POST` request to an `/admin/users/delete` endpoint.
*   *Expectation:* The system must return a `403 Forbidden` Exception, intercepting the request before it reaches the Controller.

### 3.3 Input Validation (SQLi, XSS, CSRF)
*   **SQL Injection (SQLi):** Send payloads containing `' OR 1=1 --` into login and search endpoints. Verify the `Validator` rejects them, and ensure `PDO` prepared statements safely escape them if they pass validation.
*   **Cross-Site Scripting (XSS):** Inject `<script>alert('hack')</script>` into profile update forms. Verify the `Validator` strips tags, and the response output applies `htmlspecialchars()`.
*   **CSRF:** Attempt a state-mutating request (e.g., POST `/transfer`) without the `X-CSRF-TOKEN` header. Verify the `CsrfMiddleware` explicitly blocks the transaction.

### 3.4 File Upload Security
When testing KYC identity uploads (`UploadService`):
*   Attempt to upload a `.php` or `.exe` file masquerading as a `.jpg`.
*   Verify the system asserts MIME types (`finfo`) rather than trusting the user-provided file extension.
*   Assert that files exceeding the maximum byte size throw an appropriate `ValidationException`.

## 4. Error Handling Security
A critical component of security testing is verifying what the system reveals when it breaks.
*   *Test:* Force a deliberate database connection error or constraint violation.
*   *Expectation:* Ensure the `ExceptionHandler` catches the error and returns a generic `500 Internal Server Error` JSON response. **Stack traces must never leak in the testing or production response.**

## 5. Security Test Checklist

When creating a new Feature Test, ask:
* [ ] Did I test the endpoint without authentication? (Expect 401)
* [ ] Did I test the endpoint with the wrong role? (Expect 403)
* [ ] Did I test the endpoint without a CSRF token? (Expect 403)
* [ ] Did I test the endpoint with maliciously long strings? (Expect 422)

## 6. Common Security Risks
*   **IDOR (Insecure Direct Object Reference):** A customer passing `account_id=999` (belonging to someone else) in a transfer request. Tests must ensure the Service layer validates ownership before acting.
*   **Mass Assignment:** Submitting `role=admin` during user registration. Tests must ensure the Model/Repository strictly ignores non-fillable attributes.
