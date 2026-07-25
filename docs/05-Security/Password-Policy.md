---
title: "Password Policy & Cryptography"
category: "Security"
module: "Cryptography"
last_updated: "2026-07-25"
---

# Password Policy & Cryptography

## 1. Overview
To defend against brute-force attacks, rainbow tables, and credential stuffing, the Finzo Banking System enforces a strict password policy combined with robust cryptographic hashing.

## 2. Password Strength Requirements
The `App\Presentation\Validators\AuthValidator` globally enforces the following rules for all new registrations and password resets:
- **Minimum Length**: 8 characters.
- **Complexity**: Must contain at least one uppercase letter, one lowercase letter, one number, and one special character (e.g., `!@#$%^&*`).
- **Confirmation**: `password` and `password_confirmation` payloads must strictly match.

*Note: Phase 17.1 introduced the mandatory lowercase character requirement to fully align with enterprise NIST guidelines.*

## 3. Cryptographic Hashing
Finzo strictly avoids obsolete algorithms (MD5, SHA1).
- **Algorithm**: `PASSWORD_BCRYPT` (Blowfish).
- **Work Factor (Cost)**: `12`. This deliberately slows down the hashing process to thwart GPU-based brute-forcing, taking approximately ~300ms per verification.
- **Salting**: BCrypt automatically generates and embeds a cryptographically secure random salt into the resulting hash string.

## 4. Reset & Recovery Workflow
1. User requests a password reset.
2. System generates a cryptographically secure 64-character hexadecimal token using `random_bytes(32)`.
3. The token is hashed (`hash('sha256', $token)`) before being stored in the `password_resets` table to prevent database leaks from compromising active reset links.
4. The raw token is emailed to the user.
5. Tokens explicitly expire after 60 minutes.
