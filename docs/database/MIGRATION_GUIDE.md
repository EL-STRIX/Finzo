# Database Migration & Operational Guide

This runbook outlines how to deploy, manage, and maintain the Finzo database schema using native CLI tools and migration classes.

---

## 1. Migration Management

Finzo uses a custom, framework-independent database migration subsystem (`Database\Migrator`). All schema changes are tracked as timestamped PHP classes within `database/migrations/`.

### 1.1 Running Migrations

To apply all pending schema migrations (which automatically creates a pre-migration schema backup):

```bash
php bin/finzo migrate
```

The migrator will:
1. Automatically generate a pre-migration safety backup archive.
2. Inspect the `migrations` ledger table to determine applied migrations.
3. Execute the `up()` method of each pending migration in chronological order.
4. Record the applied batch number and timestamp.

### 1.2 Rolling Back Migrations

To revert the most recent migration batch:

```bash
php bin/finzo rollback
```

To roll back all migrations to a fresh database:

```bash
php bin/finzo reset
```

---

## 2. Database Seeding

To inject essential system data (default admin roles, permissions, chart of accounts, sample branches, and system settings):

```bash
# Run system seeds (production defaults)
php bin/finzo seed system

# Run development seeds (sample accounts, staff, customers, transactions)
php bin/finzo seed dev
```

---

## 3. Creating a New Migration

1. Create a new file in `database/migrations/` using the naming pattern:
   `YYYY_MM_DD_HHMMSS_create_or_update_table_name.php`
2. Define the class using PascalCase corresponding to the migration filename:

```php
<?php

class CreateExampleTable
{
    public function up(PDO $db): void
    {
        $db->exec("
            CREATE TABLE IF NOT EXISTS example_records (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(128) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(PDO $db): void
    {
        $db->exec("DROP TABLE IF EXISTS example_records;");
    }
}
```

---

## 4. Backup & Disaster Recovery (BCDR)

Finzo provides native AES-256 encrypted backup and recovery management via `Database\BcdrManager`.

### 4.1 CLI Automated Backups
```bash
# Full encrypted backup (Schema + Data)
php bin/finzo backup full

# Schema only backup
php bin/finzo backup schema

# Data only backup
php bin/finzo backup data
```

### 4.2 CLI Restoration & Integrity Validation
```bash
# Restore from an encrypted backup archive
php bin/finzo restore <backup_file_name.sql.zip>

# Run full integrity validation (Ledger balance, audit log consistency, user state)
php bin/finzo validate
```

### 4.3 Manual Production Dump & Restore
```bash
# Manual dump
mysqldump -u finzo_db_user -p --single-transaction --routines --triggers finzo_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Manual restore
mysql -u finzo_db_user -p finzo_db < backup_file.sql
```
