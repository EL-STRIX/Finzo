# Finzo Business Continuity and Disaster Recovery (BCDR) Guide

## Version 1.0 - Phase 11 Enterprise Architecture

This document outlines the strict backup, recovery, and Disaster Recovery (DR) protocols for the Finzo Banking Management System. 

## 1. Automated Backup Architecture
The system employs `database/BcdrManager.php` to automate banking backups.
- **Location:** `storage/backups/`
- **Compression:** ZIP Archives
- **Security:** AES-256 Encryption (at-rest) using `DB_PASS`.
- **Integrity:** Backups run via `--single-transaction` to ensure transactional consistency without table locking.

## 2. Retention Policy
Automated retention drops local backups older than **30 Days**. Enterprise administrators must sync `storage/backups/` to an offsite S3-compatible cold storage facility for long-term compliance archiving.

## 3. Recovery Objectives
- **Recovery Time Objective (RTO):** 15 Minutes. (Time required to run `php bin/finzo restore`).
- **Recovery Point Objective (RPO):** 24 Hours (default daily backup), adjustable to 1 Hour depending on cron frequency.

## 4. Disaster Recovery Runbook

### Scenario A: Accidental Data Deletion / Corruption
1. Shut down the application layer to prevent further corruption (Enable Maintenance Mode).
2. Locate the most recent valid backup in `storage/backups/`.
3. Execute restore:
   ```bash
   php bin/finzo restore backup_full_2026_01_01_120000.sql.zip
   ```
4. Validate ledger integrity:
   ```bash
   php bin/finzo validate
   ```
5. Resume application operations.

### Scenario B: Failed Database Migration
The Finzo CLI automatically takes a `schema` snapshot before executing `php bin/finzo migrate`.
If a migration destroys production data:
1. Identify the pre-migration snapshot (e.g., `backup_schema_...zip`).
2. Run `php bin/finzo restore <snapshot>`.
3. Inform the engineering team to debug the failed migration.

### Scenario C: Total Hardware / Server Loss
1. Provision a new server instance.
2. Clone the Finzo codebase from the remote repository.
3. Download the latest encrypted backup from offsite cold storage into `storage/backups/`.
4. Restore the database: `php bin/finzo restore <backup_file>`.
5. Run the integrity validator: `php bin/finzo validate`.
6. Repoint DNS to the new server.

## 5. Security & Verification
NEVER assume a backup is valid until `php bin/finzo validate` returns `[OK] Ledger is perfectly balanced`. This utility automatically audits the double-entry accounting engine for mismatches after a restore.
