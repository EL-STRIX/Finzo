# Core Banking Test Cases Reference

This catalog details the automated and manual verification scenarios covering Finzo's primary operational flows.

---

## 1. Authentication & Security Test Scenarios

| Test ID | Test Scenario | Preconditions | Steps / Input | Expected Result |
|---|---|---|---|---|
| **SEC-01** | Brute-force rate limiting on login | Active user account | Submit 6 consecutive incorrect passwords within 2 minutes | The 6th attempt is blocked with HTTP `429 Too Many Requests` and rate limit cooldown headers. |
| **SEC-02** | Session timeout on inactivity | User logged in | Wait for 31 minutes without sending HTTP requests | Any subsequent request returns HTTP `401 Unauthorized` or redirects to login with session expired notice. |
| **SEC-03** | CSRF protection on POST requests | Valid session | Send `POST /api/v1/user/transfer` without `X-CSRF-Token` header | Request is immediately rejected with HTTP `403 Forbidden` / CSRF token validation failure. |
| **SEC-04** | Role privilege boundary enforcement | User logged in as teller | Attempt `POST /api/v1/admin/kyc/review` | Request is rejected with HTTP `403 Forbidden` (`checkAdminPermission` failure). |

---

## 2. Customer Onboarding & KYC Test Scenarios

| Test ID | Test Scenario | Preconditions | Steps / Input | Expected Result |
|---|---|---|---|---|
| **KYC-01** | Profile completion with missing 4-digit PIN | User in `pending` state | Submit Step 4 with empty `txn_pin` or 2-digit PIN | Validation fails with HTTP `422`: `"Transaction PIN must be exactly 4 digits"`. |
| **KYC-02** | Profile completion with valid PIN | User in `pending` state | Submit valid profile with 4-digit numeric PIN `7890` | Submission transitions user to `under_review`; pending account created with bcrypt-hashed PIN. |
| **KYC-03** | Admin KYC Approval | Application in `under_review` | Admin with `approve_application` permission calls approve endpoint | User status becomes `active`; account status becomes `active`; account number is assigned; custom PIN hash is preserved. |
| **KYC-04** | Admin KYC Rejection | Application in `under_review` | Admin calls reject endpoint with rejection reason | User status becomes `rejected`; rejection reason stored; user allowed to resubmit updated documents. |

---

## 3. Financial Transactions & Ledger Test Scenarios

| Test ID | Test Scenario | Preconditions | Steps / Input | Expected Result |
|---|---|---|---|---|
| **TXN-01** | Domestic fund transfer with valid PIN | Sender balance: $1,000; Recipient balance: $500 | Transfer $200 with correct 4-digit PIN | Sender balance becomes $800; Recipient balance becomes $700; ledger journal line debits match credits; transaction ID generated. |
| **TXN-02** | Domestic fund transfer with invalid PIN | Sender balance: $1,000 | Transfer $200 with wrong PIN `0000` | Request is aborted with HTTP `401 Unauthorized` (`"Invalid transaction PIN"`); zero balance change. |
| **TXN-03** | Insufficient balance transfer | Sender balance: $50 | Transfer $100 with valid PIN | Transfer fails with HTTP `422` (`"Insufficient funds"`). Zero ledger mutation. |
| **TXN-04** | Exceeding 24-hour daily limit | Account daily limit: $5,000 | Transfer $6,000 in a single transaction | Transfer is blocked by database trigger/service rule (`"Daily transfer limit exceeded"`). |
| **TXN-05** | Double-spend / Idempotency protection | Active account | Submit two identical transfer requests within 2 seconds | First transfer succeeds; second transfer is flagged as duplicate and blocked. |

---

## 4. Branch Staff Operations Test Scenarios

| Test ID | Test Scenario | Preconditions | Steps / Input | Expected Result |
|---|---|---|---|---|
| **STF-01** | Staff clock-in to start shift | Active staff member | Call `POST /api/v1/staff-portal/clock-in` | Active shift record created with current timestamp; teller allowed to process deposits. |
| **STF-02** | Over-the-counter customer deposit | Active teller shift; target account active | Process $500 deposit for account `FNZ...` | Customer account credited by $500; teller shift deposit total updated; transaction receipt generated. |
| **STF-03** | Over-the-counter withdrawal exceeding balance | Customer balance: $100 | Teller attempts $300 cash withdrawal | Operation rejected with HTTP `422` (`"Insufficient funds in customer account"`). |
| **STF-04** | Staff clock-out / end shift | Active shift with 3 deposits | Call `POST /api/v1/staff-portal/clock-out` with closing remarks | Shift status set to `completed`; shift summary finalized; teller dashboard reflects `Off Duty`. |
