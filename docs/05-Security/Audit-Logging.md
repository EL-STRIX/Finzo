---
title: "Audit Logging"
category: "Security"
module: "Logging"
last_updated: "2026-07-25"
---

# Audit & Authentication Logging

## 1. Overview
The Finzo Banking System utilizes a centralized `Logger` class (`App\Shared\Core\Logging\Logger`) to record system events, errors, and security-relevant incidents. All logs are stored securely on the filesystem and are inaccessible from the web root.

## 2. Security Events Logging
Authentication events must be audited to monitor for brute-force attacks and lateral movement.

Currently, the following events trigger log entries:
- **Failed Login Attempts:** Recorded when a user submits incorrect credentials.
- **Account Lockouts:** Logged explicitly when a user crosses the maximum failed attempts threshold.
- **Exceptions:** Uncaught exceptions and database connectivity issues are logged with a full stack trace to `storage/logs/error.log`.

## 3. Log Formatting and Safety
- Logs are strictly append-only.
- **Data Sanitization:** Passwords and sensitive PII MUST NEVER be written to the log files. Services are responsible for masking sensitive fields before passing context arrays to the `Logger`.

## 4. Future Roadmap (Phase 19+)
A dedicated database-backed `audit_logs` table is planned for Phase 19 to provide a web-based UI for Super Admins to monitor administrative actions (e.g., "Admin X changed the status of User Y").
