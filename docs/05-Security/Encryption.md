---
title: "Data Encryption & Transport Security"
category: "Security"
module: "Cryptography"
last_updated: "2026-07-25"
---

# Data Encryption & Transport Security

## 1. Overview
The Finzo Banking System mandates encryption for data both in-transit and at-rest, adhering strictly to modern banking compliance standards.

## 2. In-Transit Encryption
- **TLS Requirement:** All communication between the client and the Finzo web servers MUST occur over Transport Layer Security (TLS 1.2 or TLS 1.3).
- **HSTS Enforcement:** The application employs `SecurityHeadersMiddleware` to explicitly broadcast the `Strict-Transport-Security` HTTP header, preventing protocol downgrade attacks.
- **Secure Cookies:** When operating over HTTPS, `SessionManager` automatically flags the `PHPSESSID` cookie as `Secure`, preventing it from being transmitted over unencrypted HTTP connections.

## 3. At-Rest Encryption
- **Password Hashes:** User, Staff, and Admin passwords are never stored in plaintext. They are exclusively secured using the BCrypt hashing algorithm (`PASSWORD_BCRYPT`) with a high computational cost factor (`12`).
- **Reset Tokens:** Password reset tokens are generated using `random_bytes(32)` and are hashed using `sha256` before being inserted into the database. This guarantees that if the database is compromised, the active reset URLs cannot be reverse-engineered.

## 4. Key Management
- No cryptographic secrets or salts are hardcoded in the codebase.
- Application-level secrets (such as the pending JWT secret key) are managed strictly via environment variables (`.env`), which are excluded from version control via `.gitignore`.
