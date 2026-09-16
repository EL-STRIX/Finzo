# Branch Staff API Reference

The Branch Staff API powers the teller workstation, enabling customer account lookups, over-the-counter cash deposits, withdrawals, real-time teller shifts, and transaction receipts.

---

## 1. Endpoints Overview

| Method | Endpoint | Description | Permission Required |
|---|---|---|---|
| `GET` | `/api/v1/staff-portal/session` | Inspect active teller session and current shift | Authenticated Staff |
| `GET` | `/api/v1/staff-portal/dashboard` | Retrieve real-time shift analytics and metrics | Authenticated Staff |
| `POST` | `/api/v1/staff-portal/clock-in` | Clock in to start a new daily teller shift | Authenticated Staff |
| `POST` | `/api/v1/staff-portal/clock-out` | End shift with cash balancing remarks | Authenticated Staff |
| `GET` | `/api/v1/staff-portal/account-lookup` | Look up customer details by account number | Authenticated Staff |
| `POST` | `/api/v1/staff-portal/deposit` | Execute over-the-counter cash deposit | `process_transactions` |
| `POST` | `/api/v1/staff-portal/withdrawal` | Execute customer cash withdrawal | `process_transactions` |
| `GET` | `/api/v1/staff-portal/receipt/{id}` | Generate black-and-white receipt payload | Authenticated Staff |

---

## 2. Endpoint Specifications

### 2.1 Teller Clock-In (Start Shift)
Initializes an operational teller shift for the day.

- **URL:** `POST /api/v1/staff-portal/clock-in`
- **Headers:** `X-CSRF-Token: <token>`

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Shift started successfully",
  "data": {
    "shift_status": "active",
    "clock_in_time": "09:00 AM",
    "shift_date": "2026-09-14"
  }
}
```

---

### 2.2 Customer Account Lookup
Retrieves customer verification data before executing counter transactions.

- **URL:** `GET /api/v1/staff-portal/account-lookup?account_no=FNZ2026001248`
- **Headers:** `X-CSRF-Token: <token>`

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Account resolved",
  "data": {
    "account_no": "FNZ2026001248",
    "customer_name": "Sanjay Verma",
    "account_type": "savings",
    "balance": 15420.50,
    "status": "active",
    "daily_limit_left": 45000.00
  }
}
```

---

### 2.3 Over-the-Counter Cash Deposit
Executes an immediate cash credit to the specified customer account.

- **URL:** `POST /api/v1/staff-portal/deposit`
- **Headers:** `Content-Type: application/json`, `X-CSRF-Token: <token>`

#### Request Payload
```json
{
  "account_no": "FNZ2026001248",
  "amount": 2500.00,
  "deposit_type": "cash",
  "remarks": "Cash deposit at counter"
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Deposit processed successfully",
  "data": {
    "transaction_id": "TXN9F8E7D6C5B4A3210",
    "account_no": "FNZ2026001248",
    "amount": 2500.00,
    "new_balance": 17920.50,
    "receipt_url": "/public/views/staff/staff-receipt.php?id=TXN9F8E7D6C5B4A3210"
  }
}
```

---

### 2.4 Over-the-Counter Cash Withdrawal
Processes a cash withdrawal, validating sufficient funds and account active state.

- **URL:** `POST /api/v1/staff-portal/withdrawal`
- **Headers:** `Content-Type: application/json`, `X-CSRF-Token: <token>`

#### Request Payload
```json
{
  "account_no": "FNZ2026001248",
  "amount": 1000.00,
  "remarks": "Counter cash withdrawal"
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Withdrawal processed successfully",
  "data": {
    "transaction_id": "TXN4A3B2C1D0E9F8765",
    "amount": 1000.00,
    "new_balance": 16920.50
  }
}
```

---

### 2.5 Teller Clock-Out (End Shift)
Ends the teller's active shift, records closing cash balances, and archives shift metrics.

- **URL:** `POST /api/v1/staff-portal/clock-out`
- **Headers:** `Content-Type: application/json`, `X-CSRF-Token: <token>`

#### Request Payload
```json
{
  "handover_notes": "Drawer balanced accurately. Cash forwarded to head teller."
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Shift completed successfully",
  "data": {
    "shift_status": "not_clocked_in",
    "total_deposits": 7500.00,
    "total_withdrawals": 2000.00,
    "net_flow": 5500.00
  }
}
```
