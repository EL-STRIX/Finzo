---
title: "CI Testing Integration"
version: "1.0.0"
status: "Active"
owner: "DevOps Team"
category: "Testing Operations"
last_updated: "2026-07-24"
---

# CI Testing Integration

> This document details how the automated testing suite is executed continuously within the Finzo CI/CD pipeline, acting as the primary quality gate before code merges.

## 1. Purpose

Continuous Integration (CI) enforces that no human can manually bypass the testing requirements. By automating test execution on a remote server, Finzo guarantees that code is verified in a clean, pristine environment divorced from local developer machine quirks (the "it works on my machine" problem).

*Note: For the exact environmental requirements of the CI container (e.g., PHP extensions), refer to [Test Environment](Test-Environment.md).*

## 2. GitHub Actions Workflow

Finzo uses GitHub Actions as its CI engine. The workflow configuration is defined in `.github/workflows/ci.yml`.

### 2.1 Trigger Conditions
The CI pipeline executes under two conditions:
1.  **Pull Request Validation:** Any time a Pull Request is opened or updated targeting the `main` or `staging` branches.
2.  **Push Validation:** Any direct push to the `main` branch (though this should be protected by branch rules).

## 3. Automated Execution Steps

When the CI pipeline triggers, it performs the following sequential actions:

1.  **Checkout Code:** Pulls the specific commit hash.
2.  **Setup PHP:** Installs PHP 8.4+ and required extensions (`pdo_mysql`, `pcov`).
3.  **Boot Services:** Spins up an ephemeral `mysql:8.0` database service container.
4.  **Install Dependencies:** Runs `composer install --no-interaction --prefer-dist`.
5.  **Run PHPUnit:** Executes `vendor/bin/phpunit --coverage-clover=coverage.xml`.
6.  **Coverage Gate:** Executes `php bin/check-coverage.php coverage.xml` to ensure the 95% threshold is met.

## 4. Failure Handling and Required Checks

### 4.1 Required Checks
The `ci.yml` workflow is configured as a "Required Status Check" in the repository settings. 
**If the CI pipeline fails, the Pull Request's "Merge" button is permanently disabled.**

### 4.2 Handling Failures
If CI fails, the engineer must:
1. Review the GitHub Actions console output to identify the exact failing test or coverage deficit.
2. Reproduce the failure locally (e.g., `vendor/bin/phpunit --filter test_name`).
3. Commit a fix and push to the same branch, which automatically re-triggers the CI pipeline.

## 5. Best Practices for CI Testing

*   **Speed is Critical:** The CI pipeline blocks developers. The entire test suite must execute rapidly (under 2 minutes total pipeline time). Avoid downloading massive external binaries during CI setup.
*   **Database Synchronization:** The CI pipeline relies on `Migrator.php` triggered by the test `bootstrap.php`. Ensure you never commit SQL dumps to the repository; the application must be capable of building its own schema from code.
*   **Artifact Retention:** CI is configured to output `coverage-html/` as an artifact on failure, allowing engineers to download and view exactly which lines were missed.
