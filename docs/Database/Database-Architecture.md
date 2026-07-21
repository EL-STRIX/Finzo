# Database Architecture Guide
**Version 1.0**

## 1. Design Philosophy
The Finzo Banking Management System database is engineered with strict adherence to **Enterprise 3rd Normal Form (3NF)**, maximizing data integrity while enforcing business rules at the absolute lowest level (the Database Engine).

**Core Principles:**
1. **Engine-Level Validity:** No invalid state shall ever persist, regardless of application-layer bugs. Strict `CHECK` constraints, immutable triggers, and strict foreign key bounds guarantee data quality.
2. **Double-Entry Absolute Truth:** All financial mutations are backed by an immutable double-entry ledger. Application balances act as a cached representation of the ledger's ground truth.
3. **Forensic Immutability:** Audit records and finalized ledger lines can never be altered or deleted, even by super administrators.
4. **Performance by Isolation:** Analytical reads are isolated via SQL Views.

## 2. Architectural Layers

### 2.1 The Relational Core
The base layer consists of entities strictly mapped to business domains:
- **Identity Domain:** `users`, `staff`, `admin_users`, `branches`.
- **Operational Domain:** `accounts`, `beneficiaries`, `kyc_documents`.
- **Financial Domain:** `transactions`, `chart_of_accounts`, `journal_entries`, `journal_lines`, `accounting_periods`.
- **Observability Domain:** `audit_log`, `monitoring_alerts`.

### 2.2 The Automation & Validation Layer (Triggers & Constraints)
MySQL Triggers act as the ultimate business rule firewall:
- **`trg_validate_transaction`:** Enforces velocity limits, daily thresholds, account status checks (frozen/closed), and duplicate-spending prevention.
- **`trg_validate_journal_balancing`:** Mathematically guarantees `SUM(debits) = SUM(credits)` before any journal can transition to `posted`.
- **Immutability Triggers:** `prevent_audit_update`, `prevent_audit_delete` trap and reject modification attempts to forensic tables via `SIGNAL SQLSTATE 45000`.

### 2.3 The Analytical Abstraction Layer (Views)
Database Views abstract complex, repetitive SQL joins to prevent PHP application bloat and ensure reporting consistency:
- `vw_customer_summary`
- `vw_account_summary`
- `vw_branch_summary`
- `vw_monitoring_storage`
- `vw_monitoring_operational_metrics`

## 3. Database Lifecycle & Migrations
The database structural lifecycle is governed by the native `BcdrManager` and `Migrator` PHP subsystems.
- No manual SQL is ever executed against Production.
- All structural changes are committed as timestamped PHP classes under `database/migrations/`.
- Deployments automatically execute a schema-level pre-flight backup to guarantee rollback safety.
