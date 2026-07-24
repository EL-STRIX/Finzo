---
title: "Testing Environment Configuration"
version: "1.0.0"
status: "Active"
owner: "DevOps & QA Team"
category: "Testing Infrastructure"
last_updated: "2026-07-24"
---

# Test Environment Configuration

> This document details the technical setup, required dependencies, and environmental isolation strategies required to successfully execute the Finzo automated test suite locally and in CI.

## 1. Environment Configurations

Testing Finzo requires a dedicated configuration completely independent from local development or production databases. 

### 1.1 The `.env.testing` File
To run tests locally, you must create an `.env.testing` file in the project root. The `tests/bootstrap.php` file automatically parses this file when PHPUnit initializes.

**Example `.env.testing`:**
```ini
APP_ENV=testing
DB_HOST=127.0.0.1
DB_NAME=finzo_test_db
DB_USER=root
DB_PASS=root
```
> [!WARNING]
> **Never connect the testing environment to your primary development database!** The test bootstrap will drop and recreate tables dynamically.

### 1.2 Local Testing Environment
* **OS:** Windows / Linux / macOS
* **PHP:** Version `8.4+` (Required for modern typing and attributes)
* **Database:** MySQL `8.0+`
* **Execution:** Run `vendor/bin/phpunit`

### 1.3 CI Testing Environment (GitHub Actions)
Our automated pipeline (`.github/workflows/ci.yml`) simulates a pristine Ubuntu container.
* It initializes a `mysql:8.0` service container.
* Runs `composer install`.
* Executes tests via `vendor/bin/phpunit --coverage-clover=coverage.xml`.
* Runs the `bin/check-coverage.php` gate to ensure tests exceed the 95% threshold.

## 2. Software & Dependencies

### Required PHP Extensions
Your PHP CLI installation must include:
* `pdo_mysql` (For database integration)
* `mbstring` (For payload parsing)
* `pcov` or `xdebug` (Required ONLY in CI or locally if generating coverage metrics)

### Composer Packages
Testing strictly relies on native PHPUnit. 
* `phpunit/phpunit` (^13.2) is required in `composer.json` under `require-dev`.

## 3. Database Isolation Strategy

Finzo enforces a strict, enterprise-grade database isolation protocol to prevent tests from leaving artifacts or interfering with each other (Test Flakiness).

1. **Bootstrap Provisioning (`tests/bootstrap.php`):**
   * Before any tests run, the bootstrap script intercepts the PDO connection.
   * It drops all tables and executes `Migrator.php` to rebuild a clean schema from scratch.
   * It runs the `SystemSeeder` to inject required roles/settings.
   * It creates a `_test_schema_version` marker table to cache this state and avoid rebuilding the schema on every test run.

2. **Transactional State Rollbacks (`TestCase.php`):**
   * In `setUp()`, the base TestCase calls `$this->db->beginTransaction()`.
   * The test executes, interacting with the database.
   * In `tearDown()`, the base TestCase calls `$this->db->rollBack()`.
   * **Result:** The database is instantly reverted to its pristine post-migration state. No cleanup scripts are necessary.

## 4. PHPUnit Configuration

Our `phpunit.xml` dictates the execution rules:
* `<testsuites>` defines our `Unit`, `Feature`, and `Integration` suites.
* The `bootstrap="tests/bootstrap.php"` attribute ensures our isolation layer intercepts execution.
* The `<coverage>` block natively generates `coverage.xml`, `coverage-html`, and stdout text metrics.
