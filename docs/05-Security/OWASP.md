---
title: "OWASP Top 10 Mitigation Strategy"
category: "Security"
module: "Compliance"
last_updated: "2026-07-25"
---

# OWASP Compliance Overview

## 1. Objective
The Finzo Banking System is strictly audited against the OWASP Top 10 and the OWASP Application Security Verification Standard (ASVS) Level 3 (Banking/Critical).

## 2. Mitigation Strategies for Top Vulnerabilities

### A01:2021-Broken Access Control
- **Mitigation:** The system operates on a **Deny by Default** paradigm.
- **Implementation:** The `SecurityService` and `RbacService` resolve granular permissions from the database. Routes are explicitly protected via `Auth::requireAdmin()` or `Auth::requireCustomer()`. Object-level ownership is validated inside Domain Services (e.g., verifying `$account['user_id'] === $_SESSION['user_id']`).

### A02:2021-Cryptographic Failures
- **Mitigation:** Strict transport and rest encryption.
- **Implementation:** HSTS (`Strict-Transport-Security`) is enforced via middleware. All passwords are hashed using `password_hash()` with the `PASSWORD_BCRYPT` algorithm and a minimum cost of 12. No cryptographic keys are hardcoded; all are sourced from `.env`.

### A03:2021-Injection (SQLi & XSS)
- **Mitigation:** Parameterized queries and strict output escaping.
- **Implementation:** All database queries utilize PDO Prepared Statements. No variables are concatenated into SQL strings. The `Validator` sanitizes raw inputs, and frontend views escape all variables to prevent Cross-Site Scripting (XSS).

### A04:2021-Insecure Design
- **Mitigation:** Threat modeling and Clean Architecture boundaries.
- **Implementation:** Authentication and Session handling are structurally decoupled. Business logic lives strictly in Services, entirely isolated from HTTP request manipulation.

### A05:2021-Security Misconfiguration
- **Mitigation:** Environment isolation and secure defaults.
- **Implementation:** Detailed `.env` configurations prevent debug information from leaking in production. Error reporting is disabled via `php.ini` defaults. `SessionManager` forces strict HttpOnly and Secure cookie flags.

### A07:2021-Identification and Authentication Failures
- **Mitigation:** Brute-force protection and session hardening.
- **Implementation:** `RateLimiter` throttles failed attempts. Accounts are locked out after multiple sequential failures. `SessionManager` explicitly calls `session_regenerate_id(true)` to prevent session fixation.

### A08:2021-Software and Data Integrity Failures
- **Mitigation:** Secure CI/CD pipelines and Subresource Integrity (SRI).
- **Implementation:** The infrastructure enforces CSP policies that block unauthorized external scripts. Only verified Composer packages are utilized.

## 3. ASVS Verification
During Phase 17.5, the Authentication and Authorization subsystem was independently audited against the ASVS V2 (Authentication), V3 (Session Management), and V4 (Access Control) requirements, achieving 100% compliance.
