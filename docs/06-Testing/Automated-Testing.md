---
title: "Automated Testing Standards"
version: "1.0.0"
status: "Active"
owner: "QA Automation Team"
category: "Testing Core"
last_updated: "2026-07-24"
---

# Automated Testing Standards

> This document defines the goals, rules, and lifecycle of the automated testing ecosystem within the Finzo platform, guaranteeing continuous validation and regression prevention.

## 1. Automation Objectives

The primary objective of automated testing is to replace slow, error-prone human verification with rapid, highly scalable code validation. 
* **Speed:** Verify the entire banking core in under 60 seconds.
* **Accuracy:** Remove human fatigue from complex, edge-case financial scenarios.
* **Scale:** Allow multiple developers to merge code safely without stepping on each other's toes.

## 2. Benefits

* **Refactoring Confidence:** Engineers can alter core Clean Architecture layers (like migrating from PDO to an ORM) with the certainty that automated tests will catch any breaks.
* **Cost Efficiency:** Reduces the QA manual testing cycle from days down to minutes.
* **Auditability:** Automated tests act as living documentation for business logic (e.g., how daily limits are enforced).

## 3. Supported Test Types

Finzo exclusively supports automated tests written via the PHPUnit framework. 
* **Unit Tests:** See [Unit Testing](Unit-Testing.md).
* **Integration Tests:** See [Integration Testing](Integration-Testing.md).
* **Feature Tests:** See [Integration Testing](Integration-Testing.md).

## 4. Test Execution & Continuous Validation

Tests are automatically executed on two primary axes:
1. **Pre-Commit (Local):** Developers are required to run `vendor/bin/phpunit --no-coverage` to instantly validate their local changes.
2. **Post-Commit (Pipeline):** The CI/CD engine automatically triggers the full suite upon Pull Request creation. 

*Note: For detailed CI gating instructions, refer to [Test Environment](Test-Environment.md).*

## 5. Automation Standards

* **No Flakiness Tolerance:** If a test fails 1 out of 100 times, it must be quarantined, investigated, and fixed. Flaky tests erode trust in the automation pipeline.
* **DRY Test Setup:** Use `UserFactory` and `SystemSeeder` instead of rewriting massive SQL `INSERT` statements at the top of every file.
* **Meaningful Assertions:** Do not just assert that an HTTP status is `200`. Assert the specific JSON structure, database mutation, and edge case consequences.

## 6. Maintenance & Limitations

### 6.1 Limitations
Automated integration tests verify that code acts as expected. They **cannot** verify:
* Layout shifts or CSS breakages (UI).
* UX confusion or accessibility issues.
* Unexpected third-party API behavioral shifts (because we mock them).

### 6.2 Maintenance
As the Finzo banking platform evolves, tests will inevitably break due to intentional business logic shifts. 
* **Rule:** If a business requirement changes, the corresponding automated tests must be updated in the **same Pull Request**. 
* **Rule:** Never disable or comment out a failing test to "fix it later." A failing test indicates a broken system.
