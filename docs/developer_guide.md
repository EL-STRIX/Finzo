# Developer Guide & Standards
**Version 1.0**

## 1. Naming Conventions

### 1.1 Database Objects
- **Tables**: `snake_case`, plural noun (e.g., `transactions`, `journal_entries`).
- **Columns**: `snake_case`, descriptive (e.g., `created_at`, `from_account_id`).
- **Primary Keys**: Always `id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY`. Business keys should use distinct named columns (e.g., `customer_id VARCHAR UNIQUE`).
- **Foreign Keys**: Must perfectly match the target table column name if semantic, otherwise `table_singular_id`.
- **Views**: Prefixed with `vw_` (e.g., `vw_customer_summary`).
- **Functions**: Prefixed with `fn_` (e.g., `fn_calculate_account_age`).
- **Triggers**: Prefixed with `trg_` (e.g., `trg_validate_transaction`).
- **Events**: Prefixed with `evt_` (e.g., `evt_cleanup_expired_tokens`).

### 1.2 Migrations
- **Naming**: `YYYY_MM_DD_HHMMSS_action_name.php`.
- **Class Structure**: Must implement `up(PDO $db)` and `down(PDO $db)`.

## 2. Working with Migrations & Seeders
Do NOT execute raw SQL against environments. All structural database changes must be executed via the `BcdrManager` CLI.

**To deploy a new migration:**
1. Create a timestamped file in `database/migrations/`.
2. Execute locally: `php bin/finzo migrate`. (Note: The CLI will automatically snapshot the existing schema before applying the change).

**To seed test data:**
1. Execute: `php bin/finzo seed dev`.

## 3. Developing New Repositories & Services
- **Thin Controllers, Fat Services**: Controllers must solely validate HTTP parameters. Core business logic belongs in Services.
- **Repositories**: All SQL interactions exist in `app/Repositories`. Do NOT write raw SQL in Controllers or Services.
- **Database Trigger Awareness**: When writing application logic, rely on the Database Triggers to handle data validation limits. If a limit is breached, the repository will throw a `PDOException`. The Global Exception Handler (`bootstrap/app.php`) will map `SQLSTATE 45000` to a `422` response automatically. Do not duplicate trigger validation logic defensively in PHP unless for UI feedback.
