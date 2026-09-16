# Testing Strategy & Execution Guide

The Finzo platform maintains an automated test suite guaranteeing that critical banking calculations, double-entry ledgers, access boundaries, and transactions remain regression-free.

---

## 1. Test Architecture Overview

Finzo utilizes **PHPUnit 11+** configured for strict typing and isolated execution. The test suite is organized into three distinct layers:

```
tests/
├── bootstrap.php          # Database migrator, SystemSeeder, and environment setup
├── TestCase.php           # Base class enforcing transaction isolation (rollBack on tearDown)
├── Unit/                  # Isolated domain services and DTO validation (no database required)
│   ├── Actions/
│   ├── DTOs/
│   ├── Security/
│   └── Services/
└── Integration/           # Database, repository, and controller workflow tests
    ├── Actions/
    ├── Controllers/
    ├── Repositories/
    └── Security/
```

---

## 2. Running the Test Suite

### 2.1 Complete Test Run
Run the full test suite from the repository root:

```bash
vendor/bin/phpunit
```

### 2.2 Filtered Execution
Run tests for a specific domain or test case:

```bash
# Run only Unit tests
vendor/bin/phpunit tests/Unit

# Run only Integration tests
vendor/bin/phpunit tests/Integration

# Run a specific test class
vendor/bin/phpunit tests/Unit/Services/UserServiceTest.php

# Run a specific test method
vendor/bin/phpunit --filter testValidateKycEmptyData
```

### 2.3 Code Coverage Analysis
To generate an HTML code coverage report (requires Xdebug or PCOV):

```bash
vendor/bin/phpunit --coverage-html storage/coverage
```

---

## 3. Test Database & State Isolation

1. **Dedicated Database:** Tests run against a dedicated test database (default: `finzo_test_db`) defined in `phpunit.xml` or `.env.testing`. Tests never touch production or staging data.
2. **Automated Bootstrapping:** `tests/bootstrap.php` initializes the test environment:
   - Drops and rebuilds all tables using the latest migrations.
   - Executes `SystemSeeder` to inject baseline roles, permissions, branches, and sample accounts.
3. **Per-Test Transaction Isolation:** Integration tests extending `Tests\TestCase` wrap each test method in a database transaction (`$pdo->beginTransaction()`). On `tearDown()`, the transaction is rolled back (`$pdo->rollBack()`). This guarantees that:
   - No test data persists between test runs.
   - Test execution order is independent.
   - Zero test data pollution occurs.

---

## 4. Quality & Release Gating

All Pull Requests must pass automated CI pipeline checks before merge:
- **100% Passing Tests:** Zero failures or unhandled exceptions permitted.
- **Strict Typing:** All tests must declare `declare(strict_types=1);`.
- **Coverage Baseline:** Core domain services (`UserService`, `StaffService`, `SecurityService`, `AccountService`) must maintain at least 90% branch coverage.
