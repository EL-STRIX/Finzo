# Financial Architecture Documentation
**Version 1.0**

## 1. Double-Entry Accounting Core
The Finzo database explicitly abandons naive balance updates in favor of a strict Double-Entry Accounting framework. Every financial mutation is mapped via the **Posting Engine** to guarantee absolute truth.

### 1.1 The Golden Rule of Balancing
Database triggers explicitly forbid any journal entry from transitioning to `status = 'posted'` unless:
`SUM(debit_amount) = SUM(credit_amount)`

If this equation is violated, MySQL throws `SQLSTATE 45000` and the transaction is violently aborted.

## 2. Transaction Flow (The Lifecycle)
1. **API Initiation**: Application writes a row to the `transactions` table.
2. **Database Interception**: The `BEFORE INSERT ON transactions` trigger intercepts the operation.
3. **Ledger Generation**:
   - The trigger automatically resolves the appropriate accounts from the `chart_of_accounts`.
   - It generates a unique `journal_entries` header (`JNL-...`).
   - It writes exactly one Debit line and one Credit line into `journal_lines`.
4. **Validation**: The journal is marked as `posted`, forcing the ledger balancing validation trigger to fire.
5. **Audit Trail**: A shadow copy of the financial event is silently persisted into `audit_log`.

## 3. The Chart of Accounts (COA)
The COA strictly enforces `normal_balance` definitions ('debit', 'credit').
- **Asset Accounts** (e.g., Cash): Increase via Debit, decrease via Credit.
- **Liability Accounts** (e.g., Customer Deposits): Increase via Credit, decrease via Debit.

## 4. Reversals & Settlement
- **No Deletions**: It is architecturally impossible to delete a `transactions` row or a `journal_entries` row.
- **Correction Mechanism**: If an error occurs, a compensating **Reversal Journal** must be posted that strictly inverts the debits and credits of the original operation.

## 5. Reconciliation
The API exposes Health endpoints (`public/api/health.php`) which continuously compare the aggregated balances in the `accounts` table against the raw aggregated sum in `journal_lines`. The CLI command `php bin/finzo monitor` provides real-time verification of ledger integrity.
