# API & Data Flow Architecture
**Version 1.0**

## 1. Global Request Lifecycle

1. **Client Request**: An HTTP request hits `public/api/index.php`.
2. **Bootstrap (`app.php`)**: Environment variables are loaded, and the centralized Global Exception Handler is registered to trap all fatal PHP and PDO Database errors.
3. **Routing**: The request is routed to the corresponding Controller (e.g., `StaffController`, `UserController`).
4. **Validation (Application Layer)**: The Controller parses `$_POST` and executes primitive validation (presence checks, basic limits).
5. **Business Logic (Service Layer)**: The Controller injects validated data into the Service layer (e.g., `StaffService::processDeposit()`).
6. **Persistence (Repository Layer)**: The Service interacts with Repositories (e.g., `UserRepository::updateAccountBalance()`).
7. **Absolute Enforcement (Database Layer)**: MySQL accepts the query but fires its `BEFORE` triggers. If a limit is breached, the Trigger throws `SQLSTATE 45000`.
8. **Error Routing**: The Global Exception Handler catches `SQLSTATE 45000`, extracts the error message strictly defined by the Database, and routes it to the frontend as an HTTP 422 JSON response.

## 2. Typical Data Flow: The Deposit Workflow
- **Input**: Staff inputs Account No, Amount.
- **Service**: Resolves account from `AccountRepository`.
- **Database**:
  - Validates `status == 'active'` (Trigger).
  - Inserts `transactions` row.
  - Generates Dual Journal Lines (Debiting Cash COA, Crediting Customer Deposit COA).
  - Generates immutable `audit_log` row.
- **Output**: JSON payload returning `txn_id` and updated `balance`.

## 3. Asynchronous Data Flows
- **Scheduled Events**: The MySQL engine independently spins up background threads executing `evt_cleanup_expired_tokens` (every 24 hours) and `evt_cleanup_expired_sessions` (every 1 hour).
- These events bypass the API entirely to ensure strict adherence to maintenance SLAs.
