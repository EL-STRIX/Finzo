---
title: "Test Case Standards"
version: "1.0.0"
status: "Active"
owner: "QA Team"
category: "Testing Core"
last_updated: "2026-07-24"
---

# Test Case Standards

> This document defines the standard template, naming conventions, and maintenance guidelines for writing structured Test Cases within the Finzo project.

## 1. Test Case Standards

Whether authoring automated PHPUnit functions or writing manual test tickets, the logic of the test case must be clear, deterministic, and traceable. 
A good test case answers three questions:
1. What is the precondition?
2. What action is taken?
3. What is the exact expected result?

## 2. Naming Conventions

### 2.1 Automated Tests
Test function names must clearly articulate the scenario and expected outcome.
* **Bad:** `test_transfer()`
* **Good:** `test_transfer_fails_when_balance_is_insufficient()`

### 2.2 Manual Tests
Test tickets should be concise and action-oriented.
* **Bad:** `Login test`
* **Good:** `[Auth] Customer cannot log in with invalid password`

## 3. Priority and Severity Matrices

When logging manual test cases or bugs, they must be triaged.

### Severity (Technical Impact)
* **Critical (S0):** System crash, data loss, security breach, or total inability to transact.
* **High (S1):** Major feature broken with no workaround.
* **Medium (S2):** Feature broken but a reasonable workaround exists.
* **Low (S3):** Cosmetic UI issue or minor inconvenience.

### Priority (Business Urgency)
* **P0:** Drop everything and fix immediately. Hotfix required.
* **P1:** Fix in the current sprint before release.
* **P2:** Fix in the next scheduled sprint.
* **P3:** Backlog.

## 4. Test Case Template

When writing a manual test case in our tracking software (e.g., Jira/Linear), use the following standardized format:

```markdown
**Test Case ID:** TC-AUTH-001
**Title:** Customer Account Lockout After 5 Failed Attempts
**Priority:** P1
**Severity:** S1
**Traceability:** Requires FR-SEC-12 (Account Security)

### Preconditions
1. The customer account `test@finzo.com` must exist and be 'Active'.
2. The user must be logged out.

### Test Steps
1. Navigate to the Customer Login portal.
2. Enter `test@finzo.com`.
3. Enter an incorrect password `WrongPass1!` and click Login.
4. Repeat step 3 exactly five times.
5. On the 6th attempt, enter the *correct* password `CorrectPass1!`.

### Expected Results
* Attempts 1-4 should return "Invalid Credentials".
* Attempt 5 should return "Account Locked. Too many failed attempts."
* Attempt 6 (Correct Password) should STILL return "Account Locked" and explicitly deny entry.

### Actual Results
(To be filled by the tester during execution)

### Status
[ ] Pass / [ ] Fail / [ ] Blocked
```

## 5. Traceability and Maintenance

* **Traceability:** Every test case MUST trace back to a documented business requirement. If a test case exists for a feature that is not in the architecture/requirements docs, either the documentation is missing, or the feature is unauthorized.
* **Maintenance:** When a feature is deprecated or altered, the QA Lead must update or archive the associated test cases in the tracking software to prevent false-positive failures during the next regression cycle.
