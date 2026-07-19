# Security Architecture & Threat Model

## Metadata
- **Version:** 1.0.0
- **Status:** Approved
- **Owner:** Finzo SecOps
- **Last Updated:** July 2026

## Purpose
Document the official security measures applied to the Finzo Banking Management System.

---

### OWASP Top 10 Mitigation

1. **A01:2021-Broken Access Control**
   - **Mitigation:** Strict Role-Based Access Control (RBAC) enforced in Middleware before Controller execution. Separation of \`admin_users\`, \`staff\`, and \`users\`.
2. **A02:2021-Cryptographic Failures**
   - **Mitigation:** Passwords hashed with Argon2id natively via \`password_hash()\`. TLS 1.3 enforced for transport.
3. **A03:2021-Injection**
   - **Mitigation:** 100% usage of PDO Prepared Statements. No raw SQL interpolation allowed.
4. **A04:2021-Insecure Design**
   - **Mitigation:** Finzo Enterprise Product Standards (FEPS) mandates strict review prior to feature merging.
5. **A05:2021-Security Misconfiguration**
   - **Mitigation:** Strict \`php.ini\` configuration (display_errors=off, session.cookie_httponly=1).
   
### Additional Protections
- **CSRF:** Anti-CSRF tokens dynamically injected into headers and forms for all state-mutating requests (\`POST\`, \`PUT\`, \`DELETE\`).
- **XSS:** Content Security Policy (CSP) headers and strict output encoding.

---

## Revision History
| Version | Date | Author | Summary |
|---------|------|--------|---------|
| 1.0.0 | July 2026 | SecOps | Established core threat model. |
