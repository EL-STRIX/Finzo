---
title: "Bug Reporting Guidelines"
version: "1.0.0"
status: "Active"
owner: "QA Team"
category: "Testing Operations"
last_updated: "2026-07-24"
---

# Bug Reporting Guidelines

> This document outlines the standardized workflow and template requirements for logging defects within the Finzo platform. Consistency in reporting accelerates triage and resolution.

## 1. Purpose

A poorly written bug report wastes engineering time. The goal of a bug report is to provide the engineer with exactly enough context to reproduce the defect on their local environment on the first try.

*Note: For how a bug moves through the organization after being reported, see the [Bug Life Cycle](Bug-Life-Cycle.md).*

## 2. Bug Reporting Workflow

1.  **Search First:** Before logging a new ticket, search the issue tracker to ensure it is not a known duplicate.
2.  **Verify Reproducibility:** A bug must be reproducible. Attempt to trigger the bug a second time using the exact same steps. If it only happens sporadically, explicitly mark it as "Intermittent."
3.  **Gather Evidence:** Collect console logs, PHP error logs, and screenshots.
4.  **Log the Defect:** Create the ticket using the standard template.

## 3. Required Evidence

A valid bug report for Finzo must contain:
*   **Screenshots/Video:** Essential for UI/UX bugs. If possible, open the Chrome DevTools network tab in the recording.
*   **Logs:** For backend defects, attach the relevant excerpt from `storage/logs/error.log` or the JSON response payload.
*   **Environment Info:** State whether the bug occurred on `Local`, `CI`, `Staging`, or `Production`. Include the Browser name and version if it is a frontend issue.

## 4. Severity and Priority Levels

When filing a bug, the reporter must assign an initial Severity and Priority. (See [Test Cases](Test-Cases.md) for the detailed matrix definitions).

*   **Severity (S0 - S3):** How badly is the system broken? (e.g., A misspelled word is S3. A database crash is S0).
*   **Priority (P0 - P3):** How urgently does the business need this fixed? (e.g., A broken login is P0).

## 5. Bug Report Template

Copy and paste this template into the issue tracker when logging a new defect.

```markdown
**Bug Overview:** 
[Provide a clear, 1-sentence summary of the issue]

**Environment:**
* Environment: [Staging / Production]
* Role: [Customer / Staff / Admin]
* Browser/OS: [e.g., Chrome 114 on macOS]

**Steps to Reproduce:**
1. Log in as 'Customer' using `test@finzo.com`.
2. Navigate to the 'Transfers' page.
3. Attempt to transfer $5.00 to account `ACC-999`.
4. Click 'Confirm'.

**Expected Result:**
The system should successfully transfer the funds and display a green success toast.

**Actual Result:**
The system throws a 500 Internal Server Error, and the funds are deducted from my account but not credited to the receiver.

**Evidence:**
[Attach screenshot or paste API JSON response here]
```

## 6. Best Practices

*   **One Bug Per Ticket:** Never log multiple unrelated issues in a single ticket (e.g., "The transfer failed, and also the logo on the homepage is misaligned"). Split them.
*   **Neutral Tone:** State the facts clearly without emotion or frustration.
*   **Be Specific:** Do not say "The user cannot log in." Say "A user with the Customer role cannot log in; the system hangs for 30 seconds before returning a 504 error."
