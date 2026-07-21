# Enterprise Schema Dictionary
**Version 1.0**

## 1. Relational Map (Entity Relationships)

- **One-to-Many (`branches` → `users`, `staff`, `accounts`)**: Centralizes regional mapping. Every entity must logically belong to a financial branch for reporting.
- **One-to-Many (`users` → `accounts`, `beneficiaries`, `kyc_documents`)**: A single customer entity (Customer ID) manages multiple logical accounts (Savings, Current) and beneficiaries.
- **One-to-One (`transactions` → `journal_entries`)**: Every financial transaction triggers exactly one posting entry in the double-entry ledger.
- **One-to-Many (`journal_entries` → `journal_lines`)**: A posted journal entry must contain at least two lines (1 Debit, 1 Credit) representing the fundamental theorem of accounting.

## 2. Table Definitions

### 2.1 Identity Domain
- **`branches`**: Physical/Logical regional hubs. `branch_code` is UNIQUE.
- **`users`**: Core customer profiles. `customer_id` is the primary business key. Soft-deletion is enabled (`is_deleted`). Passwords use `BCRYPT`.
- **`staff`**: Employee profiles. Associated with a specific branch. Uses RBAC (`role` ENUM).
- **`admin_users`**: Super-users and moderators. Completely decoupled from standard staff to prevent privilege escalation.

### 2.2 Operational Domain
- **`accounts`**: Financial holding structures.
  - *Constraints*: `balance >= 0` (No overdrafts).
  - *Lifecycle*: `status` ENUM ('active', 'dormant', 'frozen', 'closed').
- **`beneficiaries`**: Approved external/internal transfer targets.
- **`kyc_documents`**: Stores document paths and verification state. Only active KYC allows account creation.

### 2.3 Financial Domain
- **`transactions`**: High-level business record of money movement.
  - *Types*: 'deposit', 'withdrawal', 'transfer', 'interest', 'fee'.
  - *Constraints*: `amount > 0`.
- **`chart_of_accounts`**: Enterprise COA definitions (Assets, Liabilities, Equity, Revenue, Expenses).
- **`journal_entries` & `journal_lines`**: The immutable accounting core. Once `status = 'posted'`, records are permanently locked.
- **`accounting_periods`**: Defines financial reporting windows.

### 2.4 Observability Domain
- **`audit_log`**: Captures every API mutation, auth event, and financial insertion. Contains JSON footprints for `previous_values` and `new_values`. Strict immutable triggers prevent modification.
- **`monitoring_alerts`**: Stores threshold values for database observability (e.g., max storage size).
- **`password_resets`**: Time-boxed (24 hours) tokens for credential recovery. Auto-pruned by MySQL Event Scheduler.

## 3. Core Database Constraints
- **Referential Integrity**: `ON DELETE RESTRICT` is used globally to prevent accidental cascading data loss of financial records.
- **Data Quality Limits**:
  - `accounts.daily_limit DECIMAL(15,2)` restricts outward flow.
  - `transactions.amount CHECK (amount > 0)` prevents negative injection attacks.
