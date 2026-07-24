---
title: "Automation Governance"
version: "1.0.0"
status: "Active"
owner: "QA Architecture Team"
category: "Testing Operations"
last_updated: "2026-07-24"
---

# Automation Governance & Infrastructure

> This document outlines the architectural governance of the Finzo test automation ecosystem. It defines the structural constraints that ensure our automated testing framework remains robust, maintainable, and highly performant as the banking platform scales.

## 1. Purpose

Writing tests is easy; maintaining a suite of thousands of tests is difficult. This governance document establishes the rules that prevent "Test Rot" (slow execution times, flaky assertions, and tightly coupled test files). 

*Note: For tactical writing guidelines, see [Automated Testing Standards](Automated-Testing.md).*

## 2. Automation Architecture

The Finzo test automation architecture is built exclusively on PHPUnit and the Clean Architecture paradigm.

### 2.1 The `bootstrap.php` Gateway
The `tests/bootstrap.php` file is the architectural linchpin. It is responsible for:
*   Bypassing production dependencies and routing connections to the testing database.
*   Enforcing the `Migrator` drop/build sequence to ensure a pristine schema.
*   Injecting the `SystemSeeder` data required for baseline application boot.

**Governance Rule:** No individual test class is permitted to drop database tables, run migrations, or alter the schema. All global state manipulation must occur strictly within `bootstrap.php`.

### 2.2 The `TestCase` Contract
Every integration and feature test MUST extend `Tests\TestCase`. 
*   **Governance Rule:** The `TestCase` class acts as an immutable contract guaranteeing that `PDO->beginTransaction()` and `PDO->rollBack()` wrap every single test execution. This is the sole mechanism that prevents data bleeding.

## 3. Test Stability & Flakiness

A flaky test is a test that fails intermittently without code changes. In a CI environment, a flaky test immediately blocks developers from merging, destroying engineering velocity.

### Root Causes & Governance Fixes:
1.  **Time Reliance:** Asserting that a created timestamp matches `date('Y-m-d')` will fail precisely at midnight. 
    *   *Fix:* Tests must mock the system clock or assert relative differences (e.g., `AssertLessThan(2, time() - timestamp)`).
2.  **Order Reliance:** If `Test B` requires `Test A` to execute first (because Test A leaves data in the database), Test B is invalid.
    *   *Fix:* Tests must execute in complete isolation. You should be able to run tests in random order (`--order-by=random`) with a 100% pass rate.

## 4. Test Maintenance Rules

When refactoring a core application feature, the following maintenance rules apply:

*   **Parallel Updates:** Test modifications must exist in the exact same Pull Request as the feature code. Do not push code and say "I will fix the tests in a follow-up PR."
*   **Refactoring Tests:** Test code is production code. It must adhere to the same PSR-12 formatting standards. Duplicate setup logic should be refactored into `tests/Factories/` or `setUp()` blocks.

## 5. Future Automation Roadmap

While our current testing infrastructure covers 100% of the backend APIs and Services, the QA roadmap includes:
*   **Phase 1 (Future):** Introduce automated End-to-End (E2E) Browser Testing (e.g., Cypress/Playwright) to validate the frontend Vanilla JS/Tailwind interactions against the real API.
*   **Phase 2 (Future):** Introduce dedicated Contract Testing to ensure Finzo APIs conform perfectly to published OpenAPI schemas.
