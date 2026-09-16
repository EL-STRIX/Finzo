# OWASP ASVS Compliance & Threat Model

This document outlines Finzo's compliance with the **OWASP Application Security Verification Standard (ASVS)** and details defensive measures against the OWASP Top 10 vulnerabilities.

---

## 1. OWASP Top 10 Mitigation Matrix

| Vulnerability | Finzo Defense Mechanism | Implementation Reference |
|---|---|---|
| **A01: Broken Access Control** | Deny-by-default routing, server-side RBAC validation, resource ownership checks before mutation. | `SecurityService`, `RbacService`, `AdminAuthMiddleware` |
| **A02: Cryptographic Failures** | AES-256-GCM encryption for national IDs, Bcrypt with individual salt for passwords & PINs, TLS 1.3 in transit. | `EncryptionService`, `app.php` |
| **A03: Injection (SQLi, Command)** | 100% PDO prepared statements with bound parameters. Zero dynamic SQL string concatenation. | Repositories, PDO engine |
| **A04: Insecure Design** | Double-entry accounting ledger, database constraints (`balance >= 0`), daily velocity limits. | `transactions`, `journal_lines`, MySQL Triggers |
| **A05: Security Misconfiguration** | Centralized `ExceptionHandler` masking raw exceptions, strict security headers (CSP, HSTS). | `ExceptionHandler`, `SecurityHeadersMiddleware` |
| **A06: Vulnerable & Outdated Components** | Minimal external dependencies (native PHP 8.4 runtime), continuous dependency audits via `composer audit`. | `composer.json` |
| **A07: Identification & Authentication Failures** | Inactivity timeout (30 min), session fixation protection on login, account lockouts on repeated failures. | `SessionManager`, `AuthService`, Rate Limiter |
| **A08: Software & Data Integrity Failures** | Strict CSRF token validation on all state-changing endpoints, strict MIME verification on file uploads. | `CsrfMiddleware`, `UserService::validateKyc` |
| **A09: Security Logging & Monitoring Failures** | Immutable audit log capturing all financial mutations, login attempts, and permission checks. | `audit_log`, `EventDispatcher` |
| **A10: Server-Side Request Forgery (SSRF)** | No arbitrary external URL fetching or outbound HTTP proxying allowed from user input. | Architecture policy |

---

## 2. Threat Modeling & Attack Boundaries

### 2.1 Attacker Profiles
1. **External Adversary:** Attempts credential stuffing, API fuzzing, SQL injection, and session hijacking.
   - *Mitigation:* Rate limiter blocks brute force; TLS + HttpOnly cookies prevent interception; prepared statements neutralize SQLi.
2. **Malicious or Compromised Customer:** Attempts race-condition double-spending, negative transfer values, or accessing another user's statements.
   - *Mitigation:* Row-level database locks during transfers; engine-level `CHECK (amount > 0)` and `CHECK (balance >= 0)`; strict ownership checks (`WHERE user_id = ?`).
3. **Disgruntled or Rogue Staff Member:** Attempts unauthorized balance adjustments, unauthorized KYC approvals, or modifying audit trails.
   - *Mitigation:* Over-the-counter transactions require dual accounts and active shifts; KYC reviews require `approve_application` permission; audit logs are engine-locked against updates and deletions.

---

## 3. Pre-Deployment Security Verification Checklist

- [ ] `APP_ENV` is set to `production` in `.env`.
- [ ] `APP_DEBUG` is set to `false`. Raw PHP error reporting (`display_errors = Off`) is enforced.
- [ ] `APP_KEY` is generated (32-character high-entropy secret) and configured.
- [ ] HTTPS/TLS is actively enforced across all web server vhosts.
- [ ] Session cookies have `Secure`, `HttpOnly`, and `SameSite=Strict` attributes enabled.
- [ ] Default administrator passwords have been changed from factory defaults.
- [ ] Storage upload directories (`uploads/kyc/`, `uploads/profiles/`) have execution permissions disabled (`php_flag engine off` or Nginx `fastcgi_pass` exclusion).
- [ ] Database user permissions follow the principle of least privilege (no `DROP TABLE` permissions in application production user).
