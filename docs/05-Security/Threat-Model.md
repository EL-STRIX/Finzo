---
title: "Threat Model"
category: "Security"
module: "Analysis"
last_updated: "2026-07-25"
---

# Finzo Threat Model

## 1. Objective
This document outlines the primary threat landscapes and attacker profiles that the Finzo Banking System is engineered to defend against.

## 2. Attacker Profiles
- **Unauthenticated External Attacker:** Attempts brute-force logins, credential stuffing, SQL injection, and DDoS.
- **Authenticated Malicious Customer:** Attempts Insecure Direct Object Reference (IDOR) to view or transfer funds from accounts they do not own. Attempts to trigger XSS payloads via profile updates.
- **Compromised Staff Member:** Attempts to escalate privileges to `admin` or access restricted financial reports.
- **Network Eavesdropper (MITM):** Attempts to steal `PHPSESSID` cookies or sniff plaintext credentials over unencrypted Wi-Fi.

## 3. Threat Matrix & Mitigations

| Threat | Vulnerability Vector | Finzo Mitigation Strategy |
|--------|----------------------|---------------------------|
| **Credential Stuffing** | `/api/v1/auth/login` | `RateLimiter` and strict Account Lockout thresholds. |
| **Session Hijacking** | XSS or MITM on Cookie | `SessionManager` forces `HttpOnly` and `Secure` flags. HSTS forces TLS. |
| **Session Fixation** | Forcing a known session ID | `session_regenerate_id(true)` is called immediately upon successful authentication. |
| **Cross-Site Request Forgery (CSRF)** | Malicious external site | `CsrfMiddleware` strictly demands a valid `X-CSRF-TOKEN` for all state-changing requests. |
| **SQL Injection** | Form Inputs | PDO Prepared Statements used exclusively. |
| **Cross-Site Scripting (XSS)** | User Profile / Names | `Validator::sanitizeString()` and `htmlspecialchars()` output escaping. CSP enforced via Headers. |
| **Privilege Escalation** | Manipulating URL parameters | Dynamic database-backed RBAC (`SecurityService`) enforced globally on all Admin/Staff routes. |
| **IDOR (Data Leak)** | Manipulating `account_id` in API | Domain services explicitly cross-check requested resource IDs against `$_SESSION['user_id']`. |

## 4. Trust Boundaries
- **Internet to Application (External):** All data crossing this boundary is strictly sanitized and validated.
- **Application to Database (Internal):** Database access is restricted to the application's service account. No direct external database connections are permitted.
- **Controllers to Services (Internal):** Controllers orchestrate HTTP and authentication state; Services execute business logic. Services implicitly trust Controllers to have passed them sanitized, authenticated data.
