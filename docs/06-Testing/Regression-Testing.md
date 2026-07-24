---
title: "Regression Testing Strategy"
version: "1.0.0"
status: "Active"
owner: "QA Architecture Team"
category: "Specialized Testing"
last_updated: "2026-07-24"
---

# Regression Testing Strategy

> This document defines the regression testing protocols used to ensure that new code additions or refactors do not break or degrade previously functioning features in the Finzo platform.

## 1. Purpose and Scope

Regression testing is the backbone of continuous delivery. 
In a banking system, a "regression" could mean a previously fixed bug allowing negative transfers re-emerges because a validation rule was accidentally deleted. 
The scope of this document covers how we utilize both automated suites and manual checklists to guarantee forward-only progress.

## 2. Regression Strategy & Workflow

Our primary defense against regressions is our **Automated Test Suite**. 

### 2.1 The Automated Workflow
1. A developer modifies a core class (e.g., `TransferService`).
2. They run `vendor/bin/phpunit`.
3. The suite executes all 134+ existing tests against the codebase.
4. If a test in an unrelated domain (e.g., `AccountServiceTest`) fails, a regression has been caught. The developer must fix the implementation before committing.

## 3. Bug Verification & Test Creation

**Rule:** Every time a bug is reported and fixed, an automated test MUST be created to verify the fix.

*   **Scenario:** A bug is reported where users can transfer $0.00.
*   **Action:** The developer fixes the logic.
*   **Regression Requirement:** The developer creates `test_transfer_fails_when_amount_is_zero()`.
*   **Result:** This bug can now *never* occur again without immediately failing the CI pipeline.

## 4. Release Regression (Manual)

Before any major release is cut to Production, a manual regression sweep is performed on the Staging environment to catch UI/UX integrations that automation cannot see.

### 4.1 Critical Business Flows
The following banking scenarios must be manually verified during every release regression cycle, regardless of what code was changed:
*   Customer Login and Session Timeout.
*   Execution of a standard internal transfer between two accounts.
*   Admin approval of a pending KYC application.
*   Generation of a user's transaction statement.

## 5. Maintenance and Best Practices

*   **Pruning:** As features are completely deprecated, their associated regression tests should be removed to keep the suite fast and relevant.
*   **Flaky Tests:** A flaky test is worse than no test. If a regression test randomly fails, it masks real regressions. Quarantined flaky tests must be fixed immediately.
*   **Data Consistency:** Ensure regression tests do not rely on hardcoded dates (e.g., `2023-01-01`), as they will eventually fail as time progresses. Use mocked time or relative date assertions.
