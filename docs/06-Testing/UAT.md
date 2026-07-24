---
title: "User Acceptance Testing (UAT)"
version: "1.0.0"
status: "Active"
owner: "Product & QA Team"
category: "Specialized Testing"
last_updated: "2026-07-24"
---

# User Acceptance Testing (UAT)

> This document outlines the protocols for User Acceptance Testing, the final quality gate where business stakeholders verify that software changes meet the original business requirements before production deployment.

## 1. Purpose and Overview

While automated tests ensure the code functions technically, **UAT ensures the code functions commercially**. 
UAT is conducted by actual end-users, product managers, or designated banking staff in the Staging environment. It asks the question: *"Does this feature actually solve the problem we asked it to solve?"*

## 2. Scope and Stakeholders

*   **Scope:** UAT focuses exclusively on end-to-end business flows via the graphical user interface. Testers should not be concerned with API JSON structures or database schemas.
*   **Stakeholders:**
    *   **Product Manager:** Verifies the feature matches the PRD (Product Requirements Document).
    *   **Compliance Officer:** Verifies that new KYC or transaction flows adhere to banking regulations.
    *   **QA Lead:** Facilitates the environment, provides test data, and logs any reported defects.

## 3. Entry and Exit Criteria

### 3.1 Entry Criteria (When does UAT begin?)
*   The code has passed all automated CI pipelines (Unit, Feature, Integration).
*   The code has been successfully deployed to the Staging environment.
*   A formal UAT Test Plan (or specific JIRA tickets) has been drafted and approved.
*   Test data (e.g., test customer accounts with specific balances) has been provisioned.

### 3.2 Exit Criteria (When is UAT complete?)
*   All documented UAT scenarios have been executed.
*   No Critical (S0) or High (S1) severity defects remain open.
*   The Product Manager or designated stakeholder provides explicit, written Sign-Off.

## 4. Banking Acceptance Scenarios

During UAT for a major release, stakeholders typically run through "Day in the Life" scenarios.
Examples of common UAT scenarios:
1.  **Onboarding:** A tester attempts to register a new account, upload a mock ID, and waits for a Staff user to approve it.
2.  **Transacting:** A tester initiates a transfer that deliberately exceeds their daily limit to verify the exact phrasing of the warning message.
3.  **Auditing:** A Compliance Officer logs in as Admin and generates an audit log to verify that the tester's previous actions were accurately recorded.

## 5. Workflow and Sign-Off Process

1.  **Execution:** Stakeholders execute the provided scenarios.
2.  **Defect Logging:** If a flow feels clunky or fails, it is logged. Minor UI tweaks are often cataloged as "Fast Follow" tickets for the next sprint, while critical blockers pause the release.
3.  **Sign-Off:** Once the stakeholders are satisfied, they approve the release candidate ticket. This approval is the formal trigger for DevOps to deploy the code to Production.

## 6. Best Practices

*   **Don't script everything:** While providing a checklist is good, encourage stakeholders to use the system organically. They will often uncover UX friction points that engineers missed.
*   **Real Data Simulation:** Ensure the Staging database contains a realistic volume of anonymized or mock data. Testing a transaction table with 2 rows feels very different than testing one with 2,000 rows.
*   **Clear Communication:** UAT testers are not software engineers. Provide them with clear instructions on how to log in, what browser to use, and exactly how to report an issue.
