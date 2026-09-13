# Customer User API Reference

The Customer User API powers the retail banking portal, including identity onboarding, KYC verification (with 4-digit transaction PIN setup), account queries, beneficiary management, and security settings.

---

## 1. Endpoints Overview

| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/v1/user/profile` | Retrieve customer profile and onboarding status | Yes |
| `POST` | `/api/v1/user/profile/complete` | Complete multi-step KYC profile and set 4-digit transaction PIN | Yes |
| `GET` | `/api/v1/user/accounts` | Retrieve customer deposit accounts and current balances | Yes |
| `GET` | `/api/v1/user/beneficiaries` | List saved transfer beneficiaries | Yes |
| `POST` | `/api/v1/user/beneficiaries` | Save a new internal/external transfer beneficiary | Yes |
| `GET` | `/api/v1/user/transactions` | Query filtered transaction history | Yes |
| `POST` | `/api/v1/user/security/pin` | Update or reset 4-digit transaction PIN | Yes |

---

## 2. Endpoint Specifications

### 2.1 Complete KYC Profile & Set Transaction PIN
Submits customer identity data, encrypted document uploads, banking preferences, and configures the **4-digit numeric Transaction Security PIN (TPIN)**.

- **URL:** `POST /api/v1/user/profile/complete`
- **Headers:** `Content-Type: multipart/form-data`, `X-CSRF-Token: <token>`

#### Multipart Form Parameters
| Field | Type | Required | Description |
|---|---|---|---|
| `dob` | string | Yes | Date of birth in `YYYY-MM-DD` format (must be 18+). |
| `gender` | string | Yes | `Male`, `Female`, `Other`, or `Prefer not to say`. |
| `father_name` | string | Yes | Full legal name of father. |
| `mother_name` | string | Yes | Full legal name of mother. |
| `marital_status`| string | Yes | `Single`, `Married`, `Divorced`, `Widowed`. |
| `occupation` | string | Yes | Occupation or employment sector. |
| `annual_income` | string | Yes | Annual income range. |
| `street` | string | Yes | Residential street address. |
| `city` | string | Yes | City or municipality. |
| `state` | string | Yes | State or territory. |
| `pincode` | string | Yes | 6-digit postal code. |
| `id_type` | string | Yes | `Aadhaar`, `PAN`, `Passport`, `Voter ID`, `Driving License`. |
| `id_number` | string | Yes | Document identity number (encrypted with AES-256 at rest). |
| `account_type` | string | Yes | `savings` or `current`. |
| `branch` | integer | Yes | ID of chosen servicing bank branch. |
| `txn_pin` | string | Yes | **4-digit numeric PIN (e.g. `8426`) for authenticating future money transfers.** |
| `profile_photo` | file | Yes | JPG/PNG portrait photograph (max 5MB). |
| `id_front` | file | Yes | Front image of government ID document (max 5MB). |
| `id_back` | file | Yes | Back image of government ID document (max 5MB). |

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Profile submitted successfully for review",
  "data": {
    "status": "under_review",
    "application_no": "KYC2026000412",
    "redirect": "/public/views/user/account-pending.php"
  }
}
```

---

### 2.2 Retrieve Customer Accounts
Fetches active deposit accounts belonging to the authenticated customer.

- **URL:** `GET /api/v1/user/accounts`
- **Headers:** `X-CSRF-Token: <token>`

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Accounts retrieved successfully",
  "data": {
    "accounts": [
      {
        "id": 14,
        "account_no": "FNZ2026001248",
        "account_type": "savings",
        "balance": 15420.50,
        "status": "active",
        "opened_date": "2026-08-12",
        "daily_limit": 50000.00,
        "branch_name": "Downtown Central Branch",
        "branch_code": "FNZ-MUM-001"
      }
    ]
  }
}
```

---

### 2.3 Update / Reset Transaction PIN
Allows customers to change their 4-digit transfer PIN by verifying their current PIN or account login password.

- **URL:** `POST /api/v1/user/security/pin`
- **Headers:** `Content-Type: application/json`, `X-CSRF-Token: <token>`

#### Request Payload (PIN Mode)
```json
{
  "auth_type": "pin",
  "current_pin": "8426",
  "new_pin": "5190"
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Transaction PIN updated successfully",
  "data": {
    "has_transaction_pin": true
  }
}
```
