# Finzo API Contracts

## Overview
The Finzo backend operates entirely as a JSON API layer responding to asynchronous requests from the frontend.

## Authentication Endpoints

### 1. User Login
**POST** `/api/v1/auth/login`

**Payload:**
```json
{
  "identity": "user@example.com OR 12345678",
  "password": "securepassword",
  "csrf_token": "..."
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Login successful",
  "redirect": "/public/views/user/dashboard.php",
  "user": {
    "full_name": "John Doe",
    "email": "user@example.com",
    "status": "active",
    "account_no": "FNZ123456789",
    "account_type": "savings",
    "balance": "1050.00"
  }
}
```

### 2. Admin Login
**POST** `/api/v1/auth/admin-login`

**Payload:**
```json
{
  "email": "admin@finzo.com",
  "password": "adminpassword",
  "csrf_token": "..."
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Login successful",
  "redirect": "/public/views/admin/admin-dashboard.php"
}
```

### 3. Staff Login
**POST** `/api/v1/auth/staff-login`

**Payload:**
```json
{
  "identity": "STAFF001",
  "password": "staffpassword",
  "csrf_token": "..."
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Login successful",
  "redirect": "/public/views/staff/staff-dashboard.php"
}
```
