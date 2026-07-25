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



## 5. Security & Verification
NEVER assume a backup is valid until `php bin/finzo validate` returns `[OK] Ledger is perfectly balanced`. This utility automatically audits the double-entry accounting engine for mismatches after a restore.
