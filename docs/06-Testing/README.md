---
title: "Testing Documentation Index"
version: "1.0.0"
status: "Active"
owner: "QA Team"
category: "Testing Index"
last_updated: "2026-07-24"
---

# Testing Documentation Foundation

> This directory contains the complete testing strategies, plans, and architectural configurations ensuring the stability and security of the Finzo Enterprise Banking System.

## Purpose

The Testing Documentation provides a comprehensive overview of how quality assurance is structured within Finzo. It details the rules, environments, data isolation techniques, and execution strategies required to maintain a robust, zero-regression banking core.

## Folder Overview

The `docs/06-Testing/` directory acts as the central hub for all QA-related documentation. 

### Core Documents

* **[Test Strategy](Test-Strategy.md):** The overarching philosophy, principles, and risk-management guidelines for testing the banking system.
* **[Test Plan](Test-Plan.md):** The tactical execution plan, defining deliverables, environments, criteria, and release gating.
* **[Test Environment](Test-Environment.md):** Technical specifications for Local and CI environments, including PHPUnit configs and database provisioning.
* **[Test Data](Test-Data.md):** Guidelines for managing test users, mock data, and maintaining transactional isolation during testing.

### Supplementary Documents

* **Unit Testing:** Guidelines for testing isolated domain services.
* **Feature Testing:** Guidelines for HTTP request simulations and controller coverage.
* **Integration Testing:** Guidelines for testing database interactions and repository layers.
* **Security Testing:** Security boundary and vulnerability regression testing.

## Recommended Reading Order

For engineers newly onboarding to the Finzo project, we recommend consuming the documentation in the following order:

1. **[Test Strategy](Test-Strategy.md)** - Understand *why* and *what* we test.
2. **[Test Environment](Test-Environment.md)** - Learn how to set up your local `phpunit.xml` and `.env.testing`.
3. **[Test Data](Test-Data.md)** - Learn how to utilize factories and maintain database isolation.
4. **[Test Plan](Test-Plan.md)** - Review the deployment testing gating mechanisms.

## Testing Workflow

1. **Local Execution:** Run `vendor/bin/phpunit --no-coverage` to ensure tests pass locally before committing.
2. **CI Enforcement:** GitHub Actions automatically triggers the test suite on every PR, requiring strict adherence to our >95% coverage threshold.
3. **Isolation Check:** Ensure all new Integration tests are wrapped in database transactions using the `TestCase` tear-down routines.

## Cross References

* **Architecture:** Review [System Architecture](../ARCHITECTURE.md) for structural context.
* **Security:** Review [Security Overview](../05-Security/Security-Overview.md) for our defensive boundaries.
* **Database:** Review [Database Architecture](../03-Database/Database-Architecture.md) for schema definitions used during migrations.
