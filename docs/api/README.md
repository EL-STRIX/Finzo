# Finzo Enterprise API Reference

The Finzo API provides programmatic access to core banking operations, account lifecycle management, staff teller workflows, and administrative governance. Built on **Clean Architecture** principles, the API delivers predictable, strictly-typed JSON responses.

---

## 1. Global API Architecture

### 1.1 Base URL & Versioning
All API endpoints follow a strict URI versioning pattern:

```http
https://banking.yourdomain.com/api/v1/{resource}
```

- **Production Version:** `v1`
- **Content-Type:** `application/json` (or `multipart/form-data` for file uploads)
- **Character Encoding:** UTF-8

### 1.2 Authentication Headers
Authenticated requests require one of the following methods:

1. **Web Portal Session (Default):** HTTP-only session cookie accompanied by the anti-CSRF token header:
   ```http
   X-CSRF-Token: <csrf_token_string>
   ```
2. **Stateless Bearer Authentication:**
   ```http
   Authorization: Bearer <access_token>
   ```

---

## 2. Standard JSON Response Envelope

All API endpoints return a standardized, uniform response payload:

### 2.1 Success Response (`200 OK`, `201 Created`)
```json
{
  "success": true,
  "status": 200,
  "message": "Operation completed successfully",
  "timestamp": "2026-09-14T03:00:00+00:00",
  "data": {
    "account_no": "FNZ2026001248",
    "balance": 1500.00
  }
}
```

### 2.2 Error Response (`4xx`, `5xx`)
```json
{
  "success": false,
  "status": 422,
  "message": "Validation failed",
  "timestamp": "2026-09-14T03:00:00+00:00",
  "errors": {
    "txn_pin": "Transaction PIN must be exactly 4 digits"
  }
}
```

---

## 3. Global HTTP Status Code Mapping

| HTTP Code | Reason Phrase | Meaning & Handling |
|---|---|---|
| **200** | `OK` | Request succeeded; payload returned in `data`. |
| **201** | `Created` | New resource created (e.g., beneficiary, deposit, transfer). |
| **400** | `Bad Request` | Malformed request body or missing required parameters. |
| **401** | `Unauthorized` | Unauthenticated, session expired, or invalid transaction PIN. |
| **403** | `Forbidden` | CSRF token missing/invalid or insufficient RBAC permission. |
| **404** | `Not Found` | Requested account, customer, or transaction does not exist. |
| **422** | `Unprocessable Content` | Business rule or form validation failure. Details in `errors`. |
| **429** | `Too Many Requests` | Rate limit threshold breached. Respect `Retry-After` header. |
| **500** | `Internal Server Error` | Unexpected server error. Internal details are logged safely to `storage/logs/`. |

---

## 4. Rate Limiting Directives

Incoming requests are throttled at the application perimeter:
- **General API Routes:** 120 requests per 60-second window per IP.
- **Authentication Routes:** 5 requests per 5-minute window per IP.

Response headers indicate current quota:
- `X-RateLimit-Limit`: Maximum permitted requests in current window.
- `X-RateLimit-Remaining`: Remaining request allocation.
- `X-RateLimit-Reset`: Unix timestamp when quota resets.

---

## 5. Domain API References

- **[Authentication API](AUTH_API.md):** User, Admin, and Staff login/logout and session verification.
- **[Customer User API](USER_API.md):** KYC profile completion (with 4-digit PIN setup), account queries, and statements.
- **[Branch Staff API](STAFF_API.md):** Teller shift tracking (clock-in/out), over-the-counter deposits, withdrawals, and receipts.
- **[Administrative API](ADMIN_API.md):** KYC approvals, RBAC role management, audit log inspection, and locked accounts.
- **[Transaction API](TRANSACTION_API.md):** Domestic transfers with PIN authentication, beneficiaries, and reversals.
