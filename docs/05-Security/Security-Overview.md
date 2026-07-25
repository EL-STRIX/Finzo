---
title: "Security Ecosystem Overview"
category: "Security"
module: "Overview"
last_updated: "2026-07-25"
---

# Security Ecosystem Overview

## 1. Introduction
The Finzo Banking System operates within a highly sensitive environment. Phase 17 entirely overhauled the security subsystem to ensure compliance with enterprise banking standards, specifically targeting the OWASP ASVS guidelines. 

This document serves as the index for the Finzo Security Knowledge Base.

## 2. Core Security Pillars
Finzo's security architecture is built upon the following tightly integrated pillars:

- **[Authentication Architecture](./Authentication.md):** Details the decoupled `AuthService` and HTTP orchestration.
- **[Authorization & Access Control](./Authorization.md):** Details the Deny-by-Default middleware and controller protections.
- **[Role-Based Access Control (RBAC)](./RBAC.md):** Explains the dynamic database schemas (`roles`, `permissions`) and fast in-memory caching.
- **[Session Management](./Session-Management.md):** Outlines the strict `SessionManager` protocol (HttpOnly, Secure, SameSite, Fixation prevention).
- **[Password Policy & Cryptography](./Password-Policy.md):** Covers BCrypt hashing, salting, and password complexity enforcement.
- **[Input Validation & Sanitization](./Input-Validation.md):** Discusses the `Validator` utility, output escaping, and PDO protection against SQLi.
- **[Secure Headers Enforcement](./Secure-Headers.md):** Documents the `SecurityHeadersMiddleware` (CSP, HSTS, X-Frame-Options).
- **[Audit Logging](./Audit-Logging.md):** Details the forensic logging of authentication failures and system exceptions.
- **[Encryption & Transport](./Encryption.md):** Outlines TLS mandates and at-rest cryptographic protocols.

## 3. Compliance and Governance
- **[OWASP Mitigation Strategy](./OWASP.md):** Maps Finzo implementations directly against the OWASP Top 10 vulnerabilities.
- **[Developer Security Checklist](./Security-Checklist.md):** Mandatory pre-commit checklist for all backend engineers.
- **[Threat Model](./Threat-Model.md):** Defines the attacker profiles and the specific boundaries Finzo defends.
