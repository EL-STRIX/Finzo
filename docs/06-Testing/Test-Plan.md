---
title: "Master Test Plan"
version: "1.0.0"
status: "Active"
owner: "QA Architecture Team"
category: "Testing Plan"
last_updated: "2026-07-24"
---

# Master Test Plan

> This document defines the tactical execution plan for testing the Finzo platform. It details the testing scope, entry/exit criteria, deployment workflows, and risk management required before releasing any changes to production.

## 1. Project Overview

Finzo is a high-security retail banking backend. Due to the sensitive financial nature of the operations (transfers, deposits, customer KYC), the system requires a rigorous, non-negotiable testing gating process. This Test Plan guarantees that all components are validated across multiple environments prior to deployment.

## 2. Testing Scope

### Modules Under Test
* **Authentication:** Customer, Staff, and Admin RBAC gating, Session lifecycle, CSRF validation.
* **User KYC:** Profile onboarding, identity verification logic, file upload constraints.
* **Banking Core:** Transaction creation, balance updates, daily transfer limits, pessimistic locking, atomic deposits.
* **Error Handling:** Global exception interception and deterministic 4xx/5xx API formatting.

### Types of Testing
* Code Level Unit Testing
* HTTP API Feature Testing
* Database Integrity & Foreign Key Integration Testing
* Security Configuration Testing

## 3. Deliverables

For any sprint or release, the following QA deliverables must be satisfied:
1. **Automated Test Scripts:** Executable PHPUnit classes placed in `tests/`.
2. **CI Pipeline Result:** A successful green build logged in GitHub Actions.
3. **Coverage Report:** A Clover XML file indicating >95% statement coverage.

## 4. Test Environments

Finzo testing progresses through three distinct environments:

1. **Local Development (Local):** Engineers run isolated tests locally using a dedicated local MySQL container and `phpunit`.
2. **Continuous Integration (CI):** Headless Ubuntu container running automated checks on pull requests.
3. **Staging (UAT):** A production mirror environment where Staff/Admin users perform manual User Acceptance Testing before production rollout.

## 5. Roles & Responsibilities

| Role | Responsibility |
| :--- | :--- |
| **Backend Engineer** | Write Unit and Feature tests for newly implemented tickets. |
| **QA Lead** | Monitor coverage metrics, review integration tests, maintain `bootstrap.php`. |
| **Security Officer** | Audit security tests, verify XSS/SQLi mitigations are covered. |
| **Release Manager** | Enforce Exit Criteria and approve deployment to Production. |

## 6. Entry and Exit Criteria

### Entry Criteria (When does testing begin?)
* A Pull Request is opened.
* Code meets PSR-12 formatting standards.
* The local development environment database is seeded and migrated.

### Exit Criteria (When is testing considered complete?)
* All 134+ automated tests pass without errors, failures, or PHP warnings.
* Test coverage is strictly `>95%`.
* No regressions are detected in existing legacy tests.
* The PR is formally approved by a Senior Engineer or QA Lead.

## 7. Risks and Dependencies

| Risk | Mitigation |
| :--- | :--- |
| **Flaky Tests** | Enforce strict DB isolation via `PDO->beginTransaction()` wraps. Avoid `sleep()` or time-dependent assertions unless mocking time. |
| **Data Bleeding** | Truncate the DB fully in `bootstrap.php` upon schema mismatches and rely exclusively on transaction rollbacks during execution. |
| **Third-Party API Outages** | Mock external APIs (e.g., mail dispatchers). Never allow tests to rely on the internet. |

## 8. Reporting & Approval Process

1. **CI Enforcement:** `bin/check-coverage.php` parses the test run. If it fails, the PR is automatically blocked.
2. **Test Failures:** If a test fails, the engineer must push a fix. Failing tests are treated as P0 defects.
3. **Deployment Approval:** Once CI is green, the Release Manager merges the PR, triggering deployment to the Staging environment for final UAT.
