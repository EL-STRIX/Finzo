# Database Migration & Operational Guide

This runbook outlines how to deploy, manage, and maintain the Finzo database schema using native CLI tools and migration classes.

---

## 1. Migration Management

Finzo uses a custom, framework-independent database migration subsystem (`App\Shared\Core\Database\Migrator`). All schema changes are tracked as timestamped PHP classes within `database/migrations/`.

### 1.1 Running Migrations

To apply all pending schema migrations:

```bash
php bin/migrate
```

The migrator will:
1. Inspect the `migrations` ledger table to determine applied migrations.
2. Execute the `up()` method of each pending migration in chronological order.
3. Record the applied batch number and timestamp.

### 1.2 Rolling Back Migrations

To revert the most recent migration batch:

```bash
php bin/migrate --rollback
```

To roll back all migrations to a fresh database:

```bash
php bin/migrate --fresh
```

---

## 2. Database Seeding

To inject essential system data (default admin roles, permissions, chart of accounts, sample branches, and test teller accounts):

```bash
php bin/seed
```

Or run both migrations and seeds together:

```bash
php bin/migrate --seed
```

---

## 3. Creating a New Migration

1. Create a new file in `database/migrations/` using the naming pattern:
   `YYYY_MM_DD_HHMMSS_create_or_update_table_name.php`
2. Implement the `MigrationInterface` with `up()` and `down()` methods:

```php
<?php

namespace Database\Migrations;

use PDO;

class CreateExampleTable
{
    public function up(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE example_records (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(128) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS example_records;");
    }
}
```

---

## 4. Backup & Disaster Recovery (BCDR)

### 4.1 Production Dump
```bash
mysqldump -u finzo_db_user -p --single-transaction --routines --triggers finzo_db > backup_$(date +%Y%m%d_%H%M%S).sql
```

> **Note:** The `--single-transaction` flag ensures consistent backups without locking transactional tables, while `--triggers` and `--routines` preserve banking integrity triggers and stored procedures.

### 4.2 Restoration
```bash
mysql -u finzo_db_user -p finzo_db < backup_file.sql
```
