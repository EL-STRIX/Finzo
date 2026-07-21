# Security & Compliance Model
**Version 1.0**

## 1. Authentication Security
- **Credential Storage**: All passwords in `users`, `staff`, and `admin_users` are strictly hashed using `PASSWORD_BCRYPT`. Plain text passwords never exist at rest.
- **Session Security**: The Global `write_audit_log` function captures `session_id()`, HTTP `X-Correlation-ID`, and `IP Address` natively, mapping them to the specific authenticated identity.
- **Lockout Protections**: `failed_attempts` tracking immediately engages an account freeze (`locked_until`) if brute-forcing is detected. The `evt_cleanup_expired_sessions` MySQL event asynchronously unfreezes accounts when the penalty period expires.

## 2. Authorization Boundaries (RBAC)
- **Role Isolation**: Customers, Branch Staff, and Global Administrators exist in completely isolated, discrete physical tables (`users`, `staff`, `admin_users`). It is structurally impossible for a customer token to perform administrative privilege escalation by modifying a singular `role` column in a monolithic table.
- **Admin Hierarchy**: Admins are divided into `super_admin`, `admin`, and `moderator`. Moderation roles cannot mutate database schema or system settings.

## 3. Data Protection & Encryption at Rest
- **Backup Encryption**: The Disaster Recovery engine natively compresses and encrypts all SQL snapshots via AES-256 before writing to disk, ensuring backups stolen from cold storage cannot be read.

## 4. Compliance & Audit Logging
The `audit_log` table satisfies strict banking regulatory mandates for traceability.
- **Event Uniqueness**: Every action generates a guaranteed unique `EVT-...` identifier.
- **State Capture**: Structural mutations capture JSON snapshots of `previous_values` and `new_values` for forensic investigation.
- **Immutability Guarantee**: MySQL Triggers mathematically forbid `UPDATE` or `DELETE` operations against the `audit_log` table.

## 5. Soft-Deletion Protocol
Destructive deletes (`DELETE FROM`) are largely forbidden across Identity and Operational domains. Tables feature an `is_deleted` TINYINT flag. This guarantees historical referential integrity is preserved in the double-entry ledger even if a user account is "terminated".
