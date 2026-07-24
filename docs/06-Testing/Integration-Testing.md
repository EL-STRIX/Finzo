---
title: "Integration & Feature Testing"
version: "1.0.0"
status: "Active"
owner: "QA Architecture Team"
category: "Testing Core"
last_updated: "2026-07-24"
---

# Integration & Feature Testing

> This document defines the methodologies for verifying interactions between the architectural boundaries of the Finzo banking system, specifically connecting the HTTP, Service, and Database layers.

## 1. Purpose and Scope

While Unit Tests verify isolated logic, **Integration Tests** and **Feature Tests** verify that the layers of the Clean Architecture communicate correctly.
* **Feature Tests (`tests/Feature/`)**: Simulate real incoming HTTP requests hitting API endpoints.
* **Integration Tests (`tests/Integration/`)**: Directly test Repositories and Security Middleware against the actual MySQL testing database.

## 2. Integration Philosophy

Finzo does **not** mock the database in Integration or Feature tests. We believe that banking operations (transactions, unique constraints, foreign keys) can only be genuinely validated against a real relational database engine.

All integration tests run against an ephemeral testing container, populated via `SystemSeeder`, and managed via `PDO` rollbacks.

## 3. Layer Interactions

### 3.1 Feature Testing (Controller ↔ Service)
Feature tests instantiate the application environment and dispatch simulated JSON requests directly to the API endpoints. They verify:
* **Authentication Flows:** JWT/Session token negotiation.
* **Routing:** Verifying 404s and strict HTTP verbs.
* **Middleware Integration:** Ensuring CSRF tokens and RBAC rules accurately block malicious payloads.
* **JSON Serialization:** Guaranteeing the Controller outputs the standardized `ResponseBuilder` format.

**Best Practice:** Use the `RequestHelper::postJson()` utility to simulate payloads rather than manually building `$_POST` or `$_FILES` arrays.

### 3.2 Integration Testing (Repository ↔ Database)
Repository tests bypass the HTTP layer and directly invoke data access classes. They verify:
* **Complex SQL Joins:** Ensuring custom reports or admin views pull accurate data.
* **Database Transactions:** Verifying atomic behavior (e.g., if a debit succeeds but the credit fails, the database must roll back).
* **Constraints:** Asserting that inserting duplicate emails throws a PDO constraint violation.

## 4. Transaction Validation & Idempotency

Critical banking operations require explicit integration coverage:
* **Atomicity:** Test that transferring funds utilizes database transactions and prevents partial balance deductions.
* **Concurrency:** Test that `FOR UPDATE` (Pessimistic Locking) is applied to account rows during balance mutations.
* **Financial Precision:** Ensure database storage and PHP parsing retain exact floating-point decimals.

## 5. Authentication & Authorization Flow

When testing authenticated endpoints in Feature tests, you must simulate a logged-in state.
Rather than hitting the login endpoint manually in every test, rely on `Tests\Factories\UserFactory` to create the user, and forcibly set the `$_SESSION['user_id']` in the test's `setUp()` block.

## 6. Error Handling

Finzo translates internal Domain Exceptions into standardized JSON via the global `ExceptionHandler`.
Feature tests must assert that:
* `ValidationException` returns an HTTP `422 Unprocessable Entity`.
* `AuthenticationException` returns an HTTP `401 Unauthorized`.
* `AuthorizationException` returns an HTTP `403 Forbidden`.

## 7. Test Organization & Best Practices

* **State Bleeding:** Always extend `Tests\TestCase` to inherit the `beginTransaction()` and `rollBack()` logic. **Never** manually truncate tables during a test.
* **File Uploads:** When testing file uploads, inject temporary mock images into `$_FILES` and simulate the `UploadService` logic.
* **Cross-References:** For database configuration setup, refer to [Test Environment](Test-Environment.md). For data generation rules, refer to [Test Data](Test-Data.md).
