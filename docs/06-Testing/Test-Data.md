---
title: "Test Data Management"
version: "1.0.0"
status: "Active"
owner: "QA Data Architecture Team"
category: "Testing Configuration"
last_updated: "2026-07-24"
---

# Test Data Management

> This document outlines how test data is generated, mutated, and isolated during the automated testing lifecycle in Finzo. Proper data management is critical to preventing test flakiness and ensuring reliable assertions.

## 1. Core Principles of Test Data

1. **Never use Production Data:** Production dumps contain PII (Personally Identifiable Information) and are strictly prohibited in local testing environments or CI.
2. **Ephemeral by Design:** Data should exist only for the duration of the test that needs it.
3. **No Hardcoded IDs:** Do not assert against hardcoded IDs (`id = 1`) unless the entity is globally seeded (e.g., `role_id = 1` for Admin). Auto-increment values can fluctuate.

## 2. Global System Seeders

When `phpunit` boots, `tests/bootstrap.php` triggers the `Migrator::run()` protocol. This automatically injects **System Seeders** into the test database.

### `SystemSeeder.php`
This seeder handles the baseline configuration required for the application to function:
* System configurations (e.g., global transfer limits).
* Role enums (Admin, Staff, Customer).
* Default permissions.

**Rule:** You should *not* manually seed these tables in individual tests. They are always available.

## 3. Test Factories

For creating relational entities (Users, Accounts, Transactions), Finzo utilizes a custom Factory pattern located in `tests/Factories/`.

### The `UserFactory`
The `UserFactory` encapsulates the logic to generate fully-formed users with associated customer profiles and hashed passwords.

**Example Usage in a Test:**
```php
use Tests\Factories\UserFactory;

// Generates a user with role 'customer', hashes the password, and returns the customer UUID.
$customerId = UserFactory::createCustomer([
    'email' => 'test@bank.com',
    'password' => 'SecurePass123!'
]);

// Attach a bank account to the created customer
$accountNo = UserFactory::createAccount($customerId, [
    'account_type' => 'savings',
    'balance' => 5000.00
]);
```

**Benefits:**
* Encapsulates foreign-key relationships.
* Reduces boilerplate in the `setUp()` blocks of tests.
* Ensures test data is always compliant with the latest database schema constraints.

## 4. Test Data Cleanup & Isolation

In many legacy PHP applications, test data must be manually deleted at the end of a test using a `tearDown()` script (e.g., `$this->db->exec("DELETE FROM users")`). **Finzo entirely avoids this.**

### The Transactional Rollback Pattern
All integration and feature tests extend from `Tests\TestCase`. 
The `TestCase` class leverages `PDO` to enforce absolute database isolation:

1. **Start of Test (`setUp`):** `TestCase` issues `BEGIN TRANSACTION`.
2. **Execution:** The test creates users, alters balances, and tests logic.
3. **End of Test (`tearDown`):** `TestCase` issues `ROLLBACK`.

Because of this, **no data cleanup is required**. The database state is completely ephemeral per test, preventing any side effects from bleeding into parallel or subsequent tests.

## 5. Mocking Third-Party Data

For tests involving external dependencies (e.g., an Email Dispatcher, external KYC APIs, or SMS Gateways), data must be mocked at the abstraction boundary.

* **Rule:** Never execute an HTTP request to the internet during an automated test run.
* **Practice:** Pass mock interfaces into Services, or utilize PHPUnit's `$this->createMock()` to simulate API responses.
