---
title: "Manual Testing Guidelines"
version: "1.0.0"
status: "Active"
owner: "QA Team"
category: "Testing Core"
last_updated: "2026-07-24"
---

# Manual Testing Guidelines

> While Finzo heavily prioritizes automated regression testing, human verification remains a crucial gatekeeper for UX, exploratory, and layout validation. This document outlines the manual testing process.

## 1. The Manual Testing Process

Manual testing in Finzo is performed primarily in the **Staging (UAT) Environment**. It occurs *after* the automated test suite has passed and CI is green, but *before* the code is deployed to production.

## 2. Core Manual Testing Phases

### 2.1 Smoke Testing
A rapid, shallow verification process to ensure the primary banking pathways are operational post-deployment.
* **Goal:** Verify the application boots, customers can log in, and the dashboard loads without 500 fatal errors.

### 2.2 Sanity Testing
A focused verification of newly introduced features.
* **Goal:** If a developer implemented a new "International Transfer" feature, sanity testing manually walks through this specific UI flow to verify the buttons and modals work.

### 2.3 Exploratory Testing
Unscripted testing where QA engineers creatively attempt to break the system.
* **Goal:** Discover edge cases that automation missed. Examples include rapid double-clicking transfer buttons, navigating backwards during multi-step forms, or entering bizarre character encodings in KYC inputs.

### 2.4 Functional & UI Verification
Automated backend tests cannot verify what the user *sees*. Manual testing validates:
* **Responsiveness:** Ensure tables scale correctly on mobile devices.
* **Aesthetics:** Ensure Tailwind CSS classes align with the design system.
* **Accessibility:** Verify screen readers and keyboard navigation function correctly on critical forms.

### 2.5 Acceptance Verification (UAT)
Final sign-off performed by business stakeholders or banking staff to ensure the feature solves the actual business requirement.

## 3. Defect Discovery & Reporting Workflow

When a manual tester discovers a defect, they must file a standardized bug report.

**Reporting Workflow:**
1. **Replicate:** Ensure the bug is consistently reproducible.
2. **Log:** Document the issue in the project issue tracker. Include the exact URL, User Role, Browser/Device, and steps to reproduce.
3. **Evidence:** Attach a screenshot or video recording of the bug occurring.
4. **Severity Assignment:** Assign the severity (e.g., P0 for a crash, P3 for a UI misalignment).

## 4. Best Practices

* **Assume Malice:** Test as if you are a malicious user trying to breach the system.
* **Clear Cache:** Always clear browser cache or test in Incognito mode to ensure you are validating the latest Staging deployment.
* **Log Defect to Automation Pipeline:** Whenever a critical bug is discovered manually, a ticket must be created to author an *Automated Integration Test* mimicking the bug to ensure it never regresses in the future.
