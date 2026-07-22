---
title: "Security Architecture"
version: "1.0.0"
status: "Active"
owner: "Information Security Team"
category: "Enterprise Security"
last_updated: "2026-07-22"
---

# Security Architecture

## Table of Contents
1. [Executive Summary](#executive-summary)
2. [Security Overview](#security-overview)
3. [Purpose](#purpose)
4. [Scope](#scope)
5. [Security Goals](#security-goals)
6. [Threat Model](#threat-model)
7. [Security Principles](#security-principles)
8. [Authentication](#authentication)
9. [Authorization](#authorization)
10. [RBAC](#rbac)
11. [Permission Management](#permission-management)
12. [Session Security](#session-security)
13. [Cookie Security](#cookie-security)
14. [Password Security](#password-security)
15. [Encryption](#encryption)
16. [Secrets Management](#secrets-management)
17. [CSRF Protection](#csrf-protection)
18. [XSS Protection](#xss-protection)
19. [SQL Injection Protection](#sql-injection-protection)
20. [Input Validation](#input-validation)
21. [Output Encoding](#output-encoding)
22. [File Upload Security](#file-upload-security)
23. [API Security](#api-security)
24. [Route Protection](#route-protection)
25. [Database Security](#database-security)
26. [Logging](#logging)
27. [Audit Trail](#audit-trail)
28. [Monitoring](#monitoring)
29. [Security Headers](#security-headers)
30. [HTTPS](#https)
31. [Environment Security](#environment-security)
32. [Secure Configuration](#secure-configuration)
33. [OWASP Compliance](#owasp-compliance)
34. [Security Testing](#security-testing)
35. [Penetration Testing Strategy](#penetration-testing-strategy)
36. [Best Practices](#best-practices)
37. [Anti-Patterns](#anti-patterns)
38. [Future Improvements](#future-improvements)
39. [References](#references)
40. [Cross References](#cross-references)
41. [Glossary](#glossary)
42. [Change Log](#change-log)

## Executive Summary
The Finzo Security Architecture enforces strict Zero-Trust boundaries across the entire banking ecosystem. By adhering to OWASP Top 10 guidelines and NIST frameworks, the architecture guarantees absolute mitigation against unauthorized access, payload tampering, and data exfiltration.

## Security Overview
Security is not an afterthought; it is structurally embedded into the core Repository and Service layers. The perimeter is guarded by AWS WAF, the edge by Nginx, and the internal core by strict PHP Middleware boundaries.

## Purpose
This document codifies the security constraints developers MUST respect when modifying the Finzo application, ensuring compliance with global financial regulations (PCI-DSS, SOC2).

## Scope
The scope encompasses Cryptography, Input Validation, CSRF/XSS mitigation, Role-Based Access Control (RBAC), and Audit Logging.

## Security Goals
1. Guarantee Non-repudiation of financial transactions.
2. Ensure Zero-Downtime availability against volumetric DDOS.
3. Maintain Data Confidentiality for PII and KYC data.

## Threat Model
Finzo assumes the internal network is already compromised. 
- **Actor:** External Hacker. **Vector:** SQLi. **Mitigation:** Strict PDO emulation disabled.
- **Actor:** Internal Staff. **Vector:** IDOR. **Mitigation:** Repository-level query scoping.

## Security Principles
- **Defense in Depth**: Multiple overlapping security controls.
- **Least Privilege**: Users and Services only have the exact permissions required.

## Authentication
Users authenticate via email and password, utilizing Argon2id hashing algorithms. Mobile clients consume short-lived JWTs.

## Authorization
Tokens and Sessions carry explicit Roles. The `AuthorizationMiddleware` intercepts the request, resolving the Role against the required namespace.

## RBAC
Role-Based Access Control categorizes users into `Customer`, `Staff`, and `Admin`. Each tier operates on mutually exclusive routing domains.

## Permission Management
Staff permissions are granular (e.g., `can_approve_kyc`, `can_initiate_transfer`). The DB validates permissions before executing Services.

## Session Security
Sessions are bound to the client's IP and User-Agent. If either changes mid-session, the session is aggressively invalidated (Session Hijacking protection).

## Cookie Security
All session cookies enforce `HttpOnly`, `Secure`, and `SameSite=Strict`.

## Password Security
Passwords are hashed using `PASSWORD_ARGON2ID`. The application enforces a minimum 12-character limit and cross-references inputs with the "Have I Been Pwned" API.

## Encryption
Data at rest (S3, EBS) utilizes AES-256 block encryption.

## Secrets Management
`.env` variables are strictly managed by AWS Secrets Manager and never committed to the Git repository.

## CSRF Protection
Cross-Site Request Forgery is mitigated using synchronized token patterns (`csrf_token()`) on all state-mutating standard HTTP POST/PUT requests.

## XSS Protection
Cross-Site Scripting is mitigated using strict HTML output encoding (`htmlspecialchars(..., ENT_QUOTES | ENT_HTML5, 'UTF-8')`).

## SQL Injection Protection
`PDO::ATTR_EMULATE_PREPARES = false` guarantees native compilation of parameterized SQL queries.

## Input Validation
FormRequests strictly enforce Type, Length, and Regex patterns, rejecting payloads before Controller instantiation.

## Output Encoding
JSON APIs natively encode payload characters, preventing DOM-based XSS when parsed by React/Vue clients.

## File Upload Security
KYC Documents are streamed directly to S3. PHP `finfo` validates MIME types. Executable extensions (`.php`, `.sh`) are strictly blocked.

## API Security
All endpoints enforce Token Bucket Rate Limiting (60 requests/minute) to prevent brute-force and credential stuffing.

## Route Protection
Explicit Middleware mappings in `routes/web.php` and `routes/api.php` block unauthenticated access.

## Database Security
Database users operate with Least Privilege. The Web Application user CANNOT drop tables or alter schemas.

## Logging
Security events (Failed Logins, KYC updates) are pushed to the `audit_logs` table.

## Audit Trail
Every row mutation on the `transactions` table is inherently immutable, creating a mathematically verifiable audit trail.

## Monitoring
Datadog tracks anomalies. An alert fires if a single IP generates > 10 failed logins within 60 seconds.

## Security Headers
`Content-Security-Policy`, `X-Frame-Options: DENY`, `Strict-Transport-Security` are injected via Nginx.

## HTTPS
TLS 1.2+ is strictly enforced. Port 80 redirects immediately to Port 443 via a `301 Permanent Redirect`.

## Environment Security
Production environments strictly set `display_errors = Off` to prevent stack trace leakage.

## Secure Configuration
`php.ini` disables dangerous functions (`exec`, `shell_exec`, `system`) via the `disable_functions` directive.

## OWASP Compliance
The architecture is actively audited against the OWASP Top 10 framework, guaranteeing baseline security for broken access controls and injection flaws.

## Security Testing
CI/CD runs SAST (Static Application Security Testing) via PHPStan and Psalm to detect insecure patterns dynamically.

## Penetration Testing Strategy
Quarterly external grey-box penetration tests are mandated by compliance teams.

## Best Practices
Never trust client input. Never store secrets in code.

## Anti-Patterns
Using `md5()` or `sha1()` for password hashing. Storing KYC documents in the public `htdocs/` folder.


### Deep Dive: Broken Access Control (IDOR Mitigation)
Insecure Direct Object Reference (IDOR) occurs when an application provides direct access to objects based on user-supplied input (e.g., `GET /api/v1/accounts/5`).
- **The Threat**: User A changes the URL ID to `6` and views User B's financial data.
- **Finzo Defense**: The Finzo Architecture prevents IDOR inherently at the Repository layer.
- Whenever a standard User invokes a Read command, the `AccountRepository` automatically appends `AND user_id = :auth_id` to the SQL query.
- Even if a Controller explicitly requests ID 6, the SQL engine will execute `SELECT * FROM accounts WHERE id = 6 AND user_id = 5`.
- The database returns 0 rows, and the API throws a `404 Not Found` (intentionally obfuscating the existence of ID 6) rather than a 403.

### JWT Cryptographic Signing and Replay Attacks
JSON Web Tokens are heavily utilized for stateless API authentication.
- **Signing**: Tokens are signed utilizing HMAC SHA-256 (`HS256`). The symmetric signing key is 256 bits, generated via `/dev/urandom`, and securely injected via the environment.
- **Expiration**: JWTs are strictly limited to a 15-minute lifespan (`exp` claim).
- **Refresh Flow**: Clients must use a stateful, httpOnly, long-lived Refresh Cookie to request a new JWT silently in the background, ensuring that if a JWT is intercepted in transit, its utility is highly time-bound.
- **Replay Attacks**: For state-mutating requests (Transfers), the client must inject an `X-Idempotency-Key`. The backend stores this UUID in Redis for 24 hours. If an attacker captures the JWT and the payload and attempts to replay the exact request, the API rejects it as a duplicate transaction.

### Multi-Region Secrets Rotation
A compromised database credential in a monolithic application requires a risky manual rotation. In Finzo's ecosystem, this is entirely automated.
- AWS Secrets Manager triggers a Lambda function every 30 days.
- The Lambda function logs into the MySQL primary, executes `ALTER USER ... IDENTIFIED BY 'new_secure_random_string'`, and updates the Secret value.
- Kubernetes External Secrets Operator (ESO) detects the drift and updates the cluster `Secret`.
- The pods automatically detect the `Secret` modification and trigger a rolling restart. Because PHP uses short-lived database connections, the old pods gracefully finish their current HTTP requests, while new pods spin up utilizing the new credentials—zero downtime, zero human intervention.

### Content Security Policy (CSP) and DOM XSS
While backend HTML encoding mitigates Reflected XSS, modern Single Page Applications (SPAs) are vulnerable to DOM-based XSS.
- The Nginx reverse proxy injects a strict `Content-Security-Policy` header.
- `default-src 'self'; script-src 'self' https://trusted-cdn.com; object-src 'none';`
- This instructs the browser to refuse execution of any inline `<script>` tags and completely blocks the loading of external JavaScript from unapproved domains, neutralizing the threat of a compromised 3rd-party dependency attempting to exfiltrate session data.

### Sub-Resource Integrity (SRI)
When loading assets from a CDN (e.g., Bootstrap, Vue.js), a compromised CDN could inject malicious code.
- Finzo enforces SRI hashes on all external `<script>` and `<link>` tags.
- `<script src="https://cdn.com/vue.js" integrity="sha384-oqVuAfXRKap7fdgcCY5uykM6+R9GqQ8K/uxy9rx7HNQlGYl1kPzQho1wx4JwY8wC" crossorigin="anonymous"></script>`
- If the CDN file is altered by even a single byte, the browser's cryptographic hash check fails, and the script is instantly blocked from executing.

## Future Improvements
Implement multi-factor authentication (MFA) via TOTP (Time-Based One-Time Password) for all Customer accounts.

## References
- OWASP Top 10
- NIST Cybersecurity Framework

## Cross References
- [API Architecture](../API-Architecture.md)
- [Database Architecture](../Database-Architecture.md)

## Glossary
- **Argon2id**: A memory-hard password hashing algorithm.

## Change Log
| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-22 | Security Team | Phase 8 Security Architecture |

### Defensive Coding: Time-Based Attacks and String Comparisons
Cryptographic comparisons must be resilient against timing side-channel attacks.
- **The Threat**: If an attacker submits a forged HMAC signature, a standard == string comparison fails and returns alse at the exact character it detects a mismatch. By measuring the millisecond response time, an attacker can statistically brute-force the signature character by character.
- **Finzo Defense**: Developers are strictly banned from using == or === when comparing cryptographic hashes, API keys, or webhooks.
- **Implementation**: The backend strictly enforces the use of PHP's hash_equals() function. This function utilizes a constant-time execution algorithm. It always takes the exact same number of microseconds to execute, regardless of whether the mismatch occurs at the 1st character or the 64th character, completely neutralizing timing attacks.

### Session Fixation and Cookie Stealing
When a user authenticates, their session state transitions from "Anonymous" to "Authenticated".
- **The Threat**: An attacker generates an anonymous session ID, tricks a victim into clicking a link containing that ID (e.g., ?PHPSESSID=1234), and waits for the victim to log in. The attacker now shares the authenticated session with the victim.
- **Finzo Defense**: The Finzo Authentication Service strictly invokes session_regenerate_id(true) the exact millisecond a user successfully passes the Argon2id password check.
- This entirely destroys the old anonymous session ID and issues a brand new cryptographic ID to the browser. Any session fixation attempt is immediately severed.
- **Cookie Stealing**: Even if an attacker steals the cookie via a physical compromised device, Finzo's SessionMiddleware hashes the User-Agent and IP Address together. If the attacker attempts to inject the cookie into a different browser or uses a VPN, the hash mismatches, and the session is instantly destroyed.

### Multi-Factor Authentication (MFA) Core Implementation
Passwords alone are insufficient for enterprise financial systems. Finzo implements mandatory TOTP (Time-Based One-Time Password) for all Staff and Admin accounts.
- **The Protocol**: Finzo utilizes the RFC 6238 TOTP algorithm (Google Authenticator, Authy).
- **The Seed**: Upon enablement, the backend generates a highly secure 160-bit Base32 secret seed via andom_bytes().
- **The Flow**: When an Admin logs in, the primary LoginController validates the password but does NOT issue a JWT. Instead, it issues a temporary mfa_token and returns a 202 Accepted status. The frontend redirects to an MFA challenge screen.
- Only when the Admin submits a valid 6-digit TOTP code matching the current 30-second temporal window does the backend issue the final, highly privileged Access JWT.


### Secrets Exfiltration Mitigation (SSRF)
Server-Side Request Forgery (SSRF) allows an attacker to abuse the server to make unauthorized HTTP requests to internal subnets (e.g., querying the AWS Metadata API 169.254.169.254 to steal IAM credentials).
- **The Threat**: An attacker uses the /api/v1/avatar/upload_from_url endpoint, passing http://169.254.169.254/latest/meta-data/ instead of a valid image URL. The server blindly fetches it and returns the AWS credentials to the attacker.
- **Finzo Defense**: All external HTTP calls made by the PHP backend (using Guzzle or cURL) are routed through an internal proxy or explicitly validated against an SSRF block-list.
- The SsrfValidator resolves the DNS of the target URL *before* initiating the connection. If the IP resolves to 127.0.0.1, 10.x.x.x, 172.16.x.x, 192.168.x.x, or 169.254.169.254, the request is instantly blocked and flagged as a critical security event.

### XML External Entity (XXE) Injection
Financial integrations often rely on legacy SOAP/XML payloads instead of modern JSON APIs.
- **The Threat**: An attacker submits a malicious XML payload containing an External Entity definition (<!ENTITY xxe SYSTEM "file:///etc/passwd">). If the XML parser is improperly configured, it will read the server's password file and embed it into the XML response.
- **Finzo Defense**: PHP's libxml_disable_entity_loader(true) is strictly enforced globally. 
- All XML parsing is executed using DOMDocument or SimpleXMLElement with external entities explicitly disabled, rendering XXE attacks mathematically impossible against the Finzo API.

### Cryptographic Failure Mitigation
Relying on deprecated cryptographic algorithms compromises data confidentiality and violates PCI-DSS compliance.
- **Symmetric Encryption**: Finzo exclusively utilizes AES-256-GCM for encrypting PII data at rest in the database (e.g., social security numbers). GCM provides Authenticated Encryption with Associated Data (AEAD), meaning any tampering with the ciphertext is immediately detected before decryption occurs, preventing padding oracle attacks.
- **Random Number Generation**: and() and mt_rand() are strictly banned across the entire codebase because they are deterministic and predictable. All cryptographic seeds, password reset tokens, and UUIDs are generated exclusively using andom_bytes(), which draws entropy directly from the OS /dev/urandom.

### Role Escalation and Mass Assignment Defense
A common vulnerability in rapid application development frameworks is Mass Assignment.
- **The Threat**: A user submits a POST request to update their profile: {"name": "John", "is_admin": 1}. If the backend blindly maps the JSON payload directly to the Database Model, the user just successfully escalated their privileges to an administrator.
- **Finzo Defense**: The Finzo Architecture strictly utilizes Data Transfer Objects (DTOs) and FormRequest validation classes.
- The UpdateProfileRequest explicitly defines 
ame as the ONLY allowed field.
- The UserController extracts the data via $request->validated(), completely ignoring any unapproved fields injected by the attacker.
- Furthermore, the User database model explicitly utilizes a $fillable array or strictly guarded properties to provide a secondary layer of defense against accidental mass assignment vulnerabilities at the Repository level.


### Rate Limiting vs Resource Exhaustion
While the API Gateway mitigates volumetric DDoS attacks, Application-Layer DoS attacks target specific, computationally expensive endpoints (e.g., /api/v1/reports/generate-annual-statement).
- **The Threat**: An attacker authenticated as a standard user scripts 1,000 requests per second to the report generation endpoint. The CPU is exhausted instantly.
- **Finzo Defense**: Finzo utilizes multiple, tiered Rate Limits.
- **Global Rate Limit**: 60 requests per minute per IP for standard API browsing.
- **Expensive Endpoint Rate Limit**: The report generation endpoint enforces a strict limit of 1 request per 15 minutes per User ID.
- This is enforced via Atomic Locks in Redis. When the first request hits, an atomic lock eport_generation_{user_id} is acquired for 15 minutes. Any subsequent requests immediately fail with a 429 Too Many Requests, completely protecting the application servers from algorithmic resource exhaustion.

### Dependency Vulnerability Management
Vulnerabilities in 3rd-party Composer packages (like Guzzle, Monolog, or specific cryptographic libraries) can compromise the entire application.
- **The Threat**: A CVE is announced for a specific version of a widely used library. Attackers immediately begin scanning public endpoints attempting to exploit it.
- **Finzo Defense**: The Finzo CI/CD pipeline enforces strict Software Composition Analysis (SCA).
- Tools like composer audit and Snyk are executed on every single Pull Request.
- If a vulnerability (CVE) is detected in any dependency (direct or transitive), the pipeline fails immediately. The PR cannot be merged into main until the developer updates the dependency to a patched version, ensuring no known vulnerabilities are ever deployed to production.


### Secrets Leakage Mitigation via Memory Scrubbing
In high-security financial applications, secrets must not linger in RAM any longer than strictly necessary.
- **The Threat**: If a PHP process crashes and dumps core, or if an attacker gains arbitrary memory read access (e.g., via a PHP extension vulnerability like Heartbleed), any passwords or encryption keys held in RAM could be exfiltrated.
- **Finzo Defense**: Finzo explicitly utilizes the Sodium cryptography library (ext-sodium) for memory-hard operations.
- When an encryption key or plaintext password is processed, the variable is explicitly overwritten with sodium_memzero() immediately after its utility is exhausted, guaranteeing the secret is physically erased from the server's RAM before the garbage collector executes.


### Zero-Trust Policy Enforcement
This policy is enforced mechanically via strict CI/CD linting. Any Pull Request that introduces a Controller endpoint without explicitly declaring its expected Role and Permission matrix via Middleware attributes is automatically rejected by the pipeline.
