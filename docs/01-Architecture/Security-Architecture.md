---
title: "Security Architecture"
version: "1.0.0"
status: "Active"
owner: "SecOps Team"
category: "Security"
last_updated: "2026-07-22"
---

# Security Architecture

## Table of Contents
1. [Authentication Framework](#authentication-framework)
2. [Authorization and RBAC](#authorization-and-rbac)
3. [Threat Mitigation](#threat-mitigation)
4. [Data Protection](#data-protection)

## Authentication Framework
The system implements a dual-mode authentication scheme:
1. **Stateful Session**: Secure, `HttpOnly`, `SameSite=Strict` cookies for browser-based dashboards.
2. **Stateless Bearer**: Cryptographic JWTs or Opque Tokens generated during login for API clients and mobile applications.

## Authorization and RBAC
Role-Based Access Control is enforced linearly:
- **Admin**: Absolute system control. Evaluated by `AdminAuthMiddleware`.
- **Staff**: Operational privileges. Evaluated by `StaffAuthMiddleware`.
- **User**: Scoped access strictly to data belonging to their identity constraint (`UserAuthMiddleware`).

## Threat Mitigation
- **SQL Injection**: Impossible due to absolute enforcement of PDO Prepared Statements.
- **XSS**: Inputs are heavily sanitized utilizing HTML Purifier. Outputs are safely encoded by the frontend.
- **Brute Force**: Managed natively by `RateLimiter` interacting with `FileCache`.

## Data Protection
Passwords are cryptographically secured using `password_hash()` utilizing the `BCRYPT` or `ARGON2I` algorithms dynamically based on environment PHP capabilities.
## Navigation
- **Parent Document**: [Overview.md](Overview.md)
- **Child Documents**: See sub-folders.
- **Previous Document**: N/A
- **Next Document**: N/A
- **Related Documents**: [Documentation Standards](Documentation-Standards.md)

## Change Log
| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-22 | Architecture Team | Initial Phase 1 Setup |
