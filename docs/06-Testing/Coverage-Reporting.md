---
title: "Coverage Reporting Standards"
version: "1.0.0"
status: "Active"
owner: "QA Architecture Team"
category: "Testing Operations"
last_updated: "2026-07-24"
---

# Coverage Reporting Standards

> This document defines how Finzo measures, interprets, and enforces Code Coverage metrics to ensure that critical banking logic is thoroughly validated.

## 1. Purpose and Coverage Philosophy

Code coverage measures the percentage of source code executed during the automated test suite. 
**Philosophy:** 100% coverage does not guarantee bug-free software, but 0% coverage guarantees regressions. Coverage is an early-warning radar system, not a vanity metric.

## 2. Coverage Goals and Minimum Requirements

*   **Global Project Threshold:** `95%` Line Coverage.
*   **Enforcement:** This is a hard technical gate. The `bin/check-coverage.php` script intercepts the PHPUnit XML output during [CI Testing](CI-Testing.md). If coverage drops below 95%, the CI pipeline fails with exit code `1`.

### 2.1 Critical Module Coverage
While the global average allows 5% leeway, the following namespaces require **100% Coverage**:
*   `App\Application\Services` (Business Logic)
*   `App\Shared\Core\Security` (RBAC and Password verification)
*   `App\Shared\Core\Validation` (Input Sanitation)

## 3. Generating Coverage Reports

To generate coverage reports locally, your PHP installation must have `pcov` or `xdebug` enabled.

**Run the following command:**
```bash
vendor/bin/phpunit --coverage-html coverage-report/
```
Open `coverage-report/index.html` in your browser to view a line-by-line breakdown of exactly which statements were not executed.

## 4. Coverage Metrics & Interpretation

PHPUnit outputs several coverage metrics. Finzo focuses primarily on **Line Coverage** (Statement Coverage).

*   **Green Lines:** The line was successfully executed during a test.
*   **Red Lines:** The line was *never* executed. (This is a gap).
*   **Yellow Lines (Branch/Path Coverage):** In an `if/else` block, only one of the branches was tested.

## 5. Improving Coverage

If your Pull Request fails the 95% coverage check, you must write tests to execute the red lines.

**Common Scenarios causing missed coverage:**
1.  **Missing Exception Tests:** You wrote a Service that throws a `ValidationException` if the account balance is low. You tested the successful transfer, but you forgot to write a test asserting the failure. The `throw` line will be marked red. 
    *   *Fix:* Write a unit test using `$this->expectException()`.
2.  **Dead Code:** You left in a helper function that is no longer used by the application.
    *   *Fix:* Delete the dead code entirely. Less code = higher coverage.

## 6. Coverage Limitations

Coverage is blind to **Assertions**.
A developer could theoretically execute 100% of a class without writing a single `$this->assertTrue()` statement. The coverage report would read 100%, but the test is completely useless because it validates nothing.

**Best Practice:** Code coverage is an automated metric, but *Assertion Quality* is the responsibility of human Code Reviewers. Reviewers must reject PRs where tests execute code but fail to assert the outcomes.
