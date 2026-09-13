# Transaction & Transfer API Reference

The Transaction API manages money transfers between customer accounts, enforcing strict double-entry ledger balancing, row-level locking, and 4-digit Transaction Security PIN verification.

---

## 1. Endpoints Overview

| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `POST` | `/api/v1/user/transfer` | Execute domestic funds transfer to beneficiary/account | Authenticated User |
| `GET` | `/api/v1/user/transactions/{id}` | Inspect detailed transaction receipt | Authenticated User |
| `POST` | `/api/v1/admin/transactions/reverse` | Administratively reverse an erroneous transaction | `process_transactions` |

---

## 2. Endpoint Specifications

### 2.1 Domestic Funds Transfer
Executes an immediate money transfer between accounts.
- **Security Requirement:** Requires the sender's **4-digit numeric Transaction Security PIN (TPIN)** configured during KYC onboarding.
- **Financial Validation:** Enforces row-level database locks, checks that `balance >= amount`, verifies destination account status, enforces 24-hour daily limits, and blocks duplicate requests within 2 minutes.

- **URL:** `POST /api/v1/user/transfer`
- **Headers:** `Content-Type: application/json`, `X-CSRF-Token: <token>`

#### Request Payload
```json
{
  "to_account_no": "FNZ999888777",
  "amount": 500.00,
  "description": "Invoice payment for consultancy",
  "txn_pin": "8426"
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Transfer completed successfully",
  "data": {
    "transaction_id": "TXNC0EA6059049D7ACA",
    "amount": 500.00,
    "to_name": "Dest Recipient",
    "to_account": "FNZ999888777",
    "new_balance": 4750.00,
    "timestamp": "2026-09-14T03:10:00+00:00"
  }
}
```

#### Error Response — Invalid PIN (401 Unauthorized)
```json
{
  "success": false,
  "status": 401,
  "message": "Invalid transaction PIN",
  "timestamp": "2026-09-14T03:10:00+00:00",
  "errors": {}
}
```

#### Error Response — Insufficient Balance (422 Unprocessable Content)
```json
{
  "success": false,
  "status": 422,
  "message": "Insufficient funds in source account",
  "timestamp": "2026-09-14T03:10:00+00:00",
  "errors": {}
}
```

---

### 2.2 Administrative Transaction Reversal
Allows authorized administrators to post a compensating reversal journal for an erroneous transaction.

- **URL:** `POST /api/v1/admin/transactions/reverse`
- **Headers:** `Content-Type: application/json`, `X-CSRF-Token: <token>`

#### Request Payload
```json
{
  "transaction_id": "TXNC0EA6059049D7ACA",
  "reason": "Customer reported duplicate accidental transfer"
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Transaction successfully reversed",
  "data": {
    "reversal_id": "REV98A1B2C3D4",
    "original_transaction_id": "TXNC0EA6059049D7ACA",
    "status": "reversed"
  }
}
```
