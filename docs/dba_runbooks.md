# Database Administrator (DBA) Runbooks & Maintenance Guide
**Version 1.0**

## 1. Fresh Environment Bootstrap (Installation)
When provisioning a new staging, production, or local development server, run the following sequence to bootstrap the entire Finzo schema completely autonomously:

```bash
# 1. Clone repository & configure .env
cp .env.example .env

# 2. Run Migration Runner (Builds initial schema and triggers)
php bin/finzo migrate

# 3. Seed System Definitions (Currencies, Super Admin, Limits)
php bin/finzo seed system

# 4. (Optional) Seed dummy data for non-production environments
php bin/finzo seed dev
```

## 2. Production Migration Deployment Sequence
During a release window, DBAs should execute the following sequence to deploy new schema changes securely:

```bash
# 1. The script automatically executes a schema-only pre-flight backup to storage/backups/
# 2. It applies all pending timestamped migrations in order
php bin/finzo migrate
```
If the migration catastrophically fails or degrades performance, roll back immediately:
```bash
php bin/finzo rollback
```

## 3. Database Integrity & Health Verification
DBAs should run the integrity check periodically or after any restore/rollback operation.

```bash
php bin/finzo validate
```
**Expected Output:**
- `[OK] Ledger is perfectly balanced.`
- `[OK] Audit Log is intact.`
- `[OK] Core application tables respond normally.`

If `[FAILED] Ledger is unbalanced!` appears, investigate the `vw_monitoring_ledger_health` view immediately. Do NOT run arbitrary UPDATE queries against `journal_entries`—compensating reversal journals must be issued through the API to fix variances.

## 4. Performance Tuning & Maintenance
- **Indexes:** The database relies on `idx_` prefixes. Do not manually create ad-hoc indexes in Production. Write a migration (e.g., `2026_01_01_000003_add_transaction_indexes.php`) and deploy it globally.
- **Event Scheduler:** Ensure MySQL `event_scheduler = ON` in the server configuration `my.cnf`. Finzo relies on background events to automatically prune expired `password_resets` tokens and unlock suspended sessions.

## 5. BCDR (Backup & Disaster Recovery)
Refer to the official [Disaster Recovery Guide](disaster_recovery.md) for full incident escalation protocols.
