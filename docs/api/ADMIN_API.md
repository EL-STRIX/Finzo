# Administrative API Reference

The Administrative API powers executive governance, KYC compliance review, RBAC permission matrices, forensic audit logging, and branch oversight.

---

## 1. Endpoints Overview

| Method | Endpoint | Description | Permission Required |
|---|---|---|---|
| `GET` | `/api/v1/admin/dashboard` | Administrative metrics, system health, and pending counters | Authenticated Admin |
| `GET` | `/api/v1/admin/kyc/pending` | List pending KYC applications awaiting verification | `approve_application` |
| `POST` | `/api/v1/admin/kyc/review` | Approve or reject a submitted customer KYC application | `approve_application` |
| `GET` | `/api/v1/admin/roles` | List all system roles and their assigned permissions | `manage_roles` |
| `POST` | `/api/v1/admin/roles/permissions` | Update granular permissions assigned to a role | `manage_roles` |
| `GET` | `/api/v1/admin/audit-logs` | Query and filter immutable forensic audit logs | `view_audit_logs` |
| `GET` | `/api/v1/admin/users/locked` | List locked accounts due to security violations | `manage_users` |
| `POST` | `/api/v1/admin/users/unlock` | Unlock customer or staff accounts | `manage_users` |

---

## 2. Endpoint Specifications

### 2.1 Review KYC Application (Approval & Activation)
Adjudicates a pending KYC application. When approved:
- The customer's account status transitions to `active`.
- An account number is assigned.
- **The customer's 4-digit transaction PIN configured during onboarding is activated for instant money transfers.**

- **URL:** `POST /api/v1/admin/kyc/review`
- **Headers:** `Content-Type: application/json`, `X-CSRF-Token: <token>`

#### Request Payload (Approve)
```json
{
  "user_id": 14,
  "action": "approve"
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Application approved successfully",
  "data": {
    "user_id": 14,
    "account_no": "FNZ2026001248",
    "status": "active"
  }
}
```

#### Request Payload (Reject)
```json
{
  "user_id": 14,
  "action": "reject",
  "reason": "Address proof document is blurry and unreadable. Please re-upload a clear scan."
}
```

---

### 2.2 RBAC Role Permissions Matrix
Retrieves all system roles and their currently assigned permission matrix.

- **URL:** `GET /api/v1/admin/roles`
- **Headers:** `X-CSRF-Token: <token>`

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "data": {
    "roles": [
      { "id": 1, "name": "super_admin", "description": "Global Root Authority" },
      { "id": 2, "name": "admin", "description": "Standard System Administrator" },
      { "id": 3, "name": "branch_manager", "description": "Branch Operations Manager" }
    ],
    "permissions": [
      { "name": "manage_users", "group_name": "User & Customer Management" },
      { "name": "process_transactions", "group_name": "Financial & Transaction Operations" },
      { "name": "approve_application", "group_name": "User & Customer Management" }
    ]
  }
}
```

---

### 2.3 Forensic Audit Logs
Queries the immutable audit trail with optional actor, role, or date filters.

- **URL:** `GET /api/v1/admin/audit-logs?actor_role=admin&limit=25`
- **Headers:** `X-CSRF-Token: <token>`

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "data": {
    "logs": [
      {
        "id": 1042,
        "event_id": "EVT98A1B2C3",
        "action_type": "kyc_approved",
        "actor_id": "ADM001",
        "actor_role": "ADMIN",
        "status": "SUCCESS",
        "ip_address": "192.168.1.100",
        "details": "Approved KYC for Sanjay Verma. Account: FNZ2026001248",
        "created_at": "2026-09-14 02:45:10"
      }
    ]
  }
}
```
