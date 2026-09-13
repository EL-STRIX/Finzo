# Authentication API Reference

The Authentication API manages session credential verification, portal routing, and session revocation across all three platform tiers (Customer, Staff, Admin).

---

## 1. Endpoints Overview

| Method | Endpoint | Description | Access |
|---|---|---|---|
| `POST` | `/api/v1/auth/login` | Authenticate retail banking customer | Public |
| `POST` | `/api/v1/auth/admin-login` | Authenticate executive administrator / moderator | Public |
| `POST` | `/api/v1/auth/staff-login` | Authenticate branch staff member / teller | Public |
| `POST` | `/api/v1/auth/logout` | Terminate session and invalidate cookies | Authenticated |
| `GET` | `/api/v1/auth/session` | Inspect active session details and identity | Authenticated |

---

## 2. Endpoint Specifications

### 2.1 Customer Login
Authenticate a retail banking customer using email address or customer ID.

- **URL:** `POST /api/v1/auth/login`
- **Headers:** `Content-Type: application/json`, `X-CSRF-Token: <token>`

#### Request Payload
```json
{
  "identity": "customer@finzo.com",
  "password": "Password123!"
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Login successful",
  "data": {
    "user_id": 14,
    "customer_id": "CUST001248",
    "full_name": "Sanjay Verma",
    "email": "customer@finzo.com",
    "status": "active",
    "redirect": "/public/views/user/dashboard.php"
  }
}
```

---

### 2.2 Administrator Login
Authenticate an administrator or compliance officer.

- **URL:** `POST /api/v1/auth/admin-login`
- **Headers:** `Content-Type: application/json`, `X-CSRF-Token: <token>`

#### Request Payload
```json
{
  "email": "admin@finzo.com",
  "password": "AdminSecurePassword123!"
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Admin authentication successful",
  "data": {
    "admin_id": 1,
    "username": "admin_root",
    "role": "super_admin",
    "redirect": "/public/views/admin/admin-dashboard.php"
  }
}
```

---

### 2.3 Staff Teller Login
Authenticate a branch staff employee using their staff identifier.

- **URL:** `POST /api/v1/auth/staff-login`
- **Headers:** `Content-Type: application/json`, `X-CSRF-Token: <token>`

#### Request Payload
```json
{
  "identity": "STF001024",
  "password": "StaffPassword123!"
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Staff login successful",
  "data": {
    "staff_id": 5,
    "identifier": "STF001024",
    "full_name": "Rohan Deshmukh",
    "designation": "teller",
    "branch_id": 2,
    "redirect": "/public/views/staff/staff-dashboard.php"
  }
}
```

---

### 2.4 User Logout
Invalidate server-side session and expire client cookies.

- **URL:** `POST /api/v1/auth/logout`
- **Headers:** `X-CSRF-Token: <token>`

#### Response (200 OK)
```json
{
  "success": true,
  "status": 200,
  "message": "Logged out successfully",
  "data": {
    "redirect": "/public/views/auth/login.php"
  }
}
```
