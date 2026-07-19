# Operations & Runbook

## Metadata
- **Version:** 1.0.0
- **Status:** Approved
- **Owner:** Finzo SRE Team
- **Last Updated:** July 2026

## Purpose
Define monitoring, logging, and incident response operations.

---

### Logging
All administrative and critical user actions are logged to the \`audit_log\` table in the database.
PHP fatal errors and warnings are written to \`storage/logs/php_error.log\` and must NEVER be displayed to the end-user (display_errors=off).

### Health Checks
- **Database:** Ensure MySQL service is bound and accepting connections on port 3306.
- **Web Server:** Ensure PHP-FPM and Nginx/Apache are active.
- **Application Status:** Access \`/api/health.php\` (to be implemented) to verify system OK.

### Disaster Recovery
- **Database Backups:** Hourly incremental and nightly full dumps.
- **RTO (Recovery Time Objective):** 4 Hours.
- **RPO (Recovery Point Objective):** 1 Hour.

---

## Revision History
| Version | Date | Author | Summary |
|---------|------|--------|---------|
| 1.0.0 | July 2026 | SRE | Baseline Runbook. |
