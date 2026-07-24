---
title: "Bug Life Cycle"
version: "1.0.0"
status: "Active"
owner: "QA Team"
category: "Testing Operations"
last_updated: "2026-07-24"
---

# Bug Life Cycle

> This document defines the exact workflow and status transitions a defect undergoes from the moment it is reported until it is permanently closed in the Finzo ecosystem.

## 1. Purpose

Defining the Bug Life Cycle ensures that no defect is forgotten, that developers do not waste time on invalid reports, and that QA independently verifies all fixes before they reach Production.

## 2. Bug States and Status Transitions

Every bug logged in the Finzo issue tracker must adhere to the following strict state transitions:

1.  **New:** A bug has just been reported. It has not yet been reviewed.
2.  **Triaged (Ready):** A QA Lead or Product Manager has reviewed the bug, confirmed it is valid, and prioritized it into a sprint backlog.
3.  **In Progress:** An engineer is actively writing code to fix the defect.
4.  **In Review:** The engineer has opened a Pull Request, and the CI automated test suite is validating the fix.
5.  **In QA (Ready for Test):** The code has been merged and deployed to Staging. The bug is now waiting for a manual tester to verify the fix.
6.  **Closed (Done):** QA has verified the fix on Staging. The bug no longer exists.

*Alternative States:*
*   **Rejected/Invalid:** The reported bug is actually a feature, or is not reproducible.
*   **Duplicate:** The bug already exists in another ticket.

## 3. The Verification Workflow

The critical phase of the Bug Life Cycle is the transition from **In QA** to **Closed**.

1.  **Deployment:** A fix must physically exist on the Staging environment before verification begins.
2.  **QA Validation:** The QA tester opens the original bug ticket and follows the exact "Steps to Reproduce."
3.  **Cross-Environment Check:** The tester must ensure they clear their cache to guarantee they are testing the latest deployment.

## 4. The Reopening Process

If a QA tester executes the steps on Staging and the bug *still occurs*, the ticket does **not** get closed.
*   **Action:** The tester changes the status from *In QA* back to *In Progress* (or *Reopened*).
*   **Comment:** The tester leaves a comment explaining why the fix failed (e.g., "The fix works on Chrome, but the button is still broken on Safari").
*   **Responsibility:** The original engineer is responsible for addressing the reopened ticket immediately.

## 5. Closure Criteria

A bug ticket can only be transitioned to **Closed** if it meets the following criteria:
1. The bug cannot be reproduced on Staging.
2. The engineer created an automated [Regression Test](Regression-Testing.md) to ensure the bug never returns.
3. CI/CD pipelines are green.

## 6. Responsibilities

*   **Reporters (QA/Product):** Responsible for writing accurate tickets in the *New* state.
*   **Triage Lead:** Responsible for moving tickets from *New* to *Triaged* and assigning Priority.
*   **Engineers:** Responsible for moving tickets to *In Progress* and delivering the fix with an automated test.
*   **QA Testers:** The *only* individuals authorized to move a ticket to *Closed*. Engineers may not close their own bugs.
