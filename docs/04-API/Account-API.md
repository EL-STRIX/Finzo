---
title: "{$cleanName} API Documentation"
version: "1.0.0"
category: "API Reference"
module: "{$cleanName}"
owner: "Backend Engineering"
last_updated: "2026-07-22"
---

# {$cleanName} API

## Table of Contents
1. [Introduction](#introduction)
2. [Authentication Requirements](#authentication-requirements)
3. [API Endpoints](#api-endpoints)
   - [GET /api/user/accounts](#endpoint-0)
   - [GET /api/user/accounts/lookup](#endpoint-1)
   - [POST /api/user/accounts/settings](#endpoint-2)
   - [GET /api/user/beneficiaries](#endpoint-3)
   - [POST /api/user/beneficiaries](#endpoint-4)
   - [GET /api/v1/user/accounts](#endpoint-5)
   - [GET /api/v1/user/accounts/lookup](#endpoint-6)
   - [POST /api/v1/user/accounts/settings](#endpoint-7)
   - [GET /api/v1/user/beneficiaries](#endpoint-8)
   - [POST /api/v1/user/beneficiaries](#endpoint-9)


## Introduction
The `{$cleanName}` module encapsulates all operations related to {$cleanName} management within the Finzo Banking System. 
This document provides enterprise-grade specifications for frontend developers, integrators, and DevOps engineers.

## Authentication Requirements
Unless specified otherwise, endpoints in this module require a Bearer token or session cookie.
Role restrictions and strict Gate evaluations are enforced at the Controller boundary.

## API Endpoints

### <a name="endpoint-0"></a> GET `/api/user/accounts`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::accounts`

**Middleware / Guards:** `UserAuthMiddleware`

#### Query Parameters (Pagination/Search)
| Name | Type | Required | Description |
|---|---|---|---|
| `page` | integer | No | Default: 1. Target page for records. |
| `per_page` | integer | No | Default: 20. Max: 100. |
| `sort` | string | No | The field to sort results by. |
| `order` | string | No | `asc` or `desc`. |
| `search` | string | No | Partial string match execution. |

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Operation successful",
    "timestamp": "2026-07-22T00:00:00Z",
    "data": {}
}
```

#### Common Error Codes
- `400 Bad Request`
- `401 Unauthorized` (Token invalid or expired)
- `403 Forbidden` (Insufficient permissions)
- `404 Not Found`
- `422 Unprocessable Entity` (Validation failure)
---

### <a name="endpoint-1"></a> GET `/api/user/accounts/lookup`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::accountLookup`

**Middleware / Guards:** `UserAuthMiddleware`

#### Query Parameters (Pagination/Search)
| Name | Type | Required | Description |
|---|---|---|---|
| `page` | integer | No | Default: 1. Target page for records. |
| `per_page` | integer | No | Default: 20. Max: 100. |
| `sort` | string | No | The field to sort results by. |
| `order` | string | No | `asc` or `desc`. |
| `search` | string | No | Partial string match execution. |

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Operation successful",
    "timestamp": "2026-07-22T00:00:00Z",
    "data": {}
}
```

#### Common Error Codes
- `400 Bad Request`
- `401 Unauthorized` (Token invalid or expired)
- `403 Forbidden` (Insufficient permissions)
- `404 Not Found`
- `422 Unprocessable Entity` (Validation failure)
---

### <a name="endpoint-2"></a> POST `/api/user/accounts/settings`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::accountSettings`

**Middleware / Guards:** `UserAuthMiddleware`

#### Request Body Example (JSON)
```json
{
    "example_field": "value"
}
```

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Operation successful",
    "timestamp": "2026-07-22T00:00:00Z",
    "data": {}
}
```

#### Common Error Codes
- `400 Bad Request`
- `401 Unauthorized` (Token invalid or expired)
- `403 Forbidden` (Insufficient permissions)
- `404 Not Found`
- `422 Unprocessable Entity` (Validation failure)
---

### <a name="endpoint-3"></a> GET `/api/user/beneficiaries`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::beneficiary`

**Middleware / Guards:** `UserAuthMiddleware`

#### Query Parameters (Pagination/Search)
| Name | Type | Required | Description |
|---|---|---|---|
| `page` | integer | No | Default: 1. Target page for records. |
| `per_page` | integer | No | Default: 20. Max: 100. |
| `sort` | string | No | The field to sort results by. |
| `order` | string | No | `asc` or `desc`. |
| `search` | string | No | Partial string match execution. |

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Operation successful",
    "timestamp": "2026-07-22T00:00:00Z",
    "data": {}
}
```

#### Common Error Codes
- `400 Bad Request`
- `401 Unauthorized` (Token invalid or expired)
- `403 Forbidden` (Insufficient permissions)
- `404 Not Found`
- `422 Unprocessable Entity` (Validation failure)
---

### <a name="endpoint-4"></a> POST `/api/user/beneficiaries`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::beneficiary`

**Middleware / Guards:** `UserAuthMiddleware`

#### Request Body Example (JSON)
```json
{
    "example_field": "value"
}
```

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Operation successful",
    "timestamp": "2026-07-22T00:00:00Z",
    "data": {}
}
```

#### Common Error Codes
- `400 Bad Request`
- `401 Unauthorized` (Token invalid or expired)
- `403 Forbidden` (Insufficient permissions)
- `404 Not Found`
- `422 Unprocessable Entity` (Validation failure)
---

### <a name="endpoint-5"></a> GET `/api/v1/user/accounts`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::accounts`

**Middleware / Guards:** `UserAuthMiddleware`

#### Query Parameters (Pagination/Search)
| Name | Type | Required | Description |
|---|---|---|---|
| `page` | integer | No | Default: 1. Target page for records. |
| `per_page` | integer | No | Default: 20. Max: 100. |
| `sort` | string | No | The field to sort results by. |
| `order` | string | No | `asc` or `desc`. |
| `search` | string | No | Partial string match execution. |

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Operation successful",
    "timestamp": "2026-07-22T00:00:00Z",
    "data": {}
}
```

#### Common Error Codes
- `400 Bad Request`
- `401 Unauthorized` (Token invalid or expired)
- `403 Forbidden` (Insufficient permissions)
- `404 Not Found`
- `422 Unprocessable Entity` (Validation failure)
---

### <a name="endpoint-6"></a> GET `/api/v1/user/accounts/lookup`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::accountLookup`

**Middleware / Guards:** `UserAuthMiddleware`

#### Query Parameters (Pagination/Search)
| Name | Type | Required | Description |
|---|---|---|---|
| `page` | integer | No | Default: 1. Target page for records. |
| `per_page` | integer | No | Default: 20. Max: 100. |
| `sort` | string | No | The field to sort results by. |
| `order` | string | No | `asc` or `desc`. |
| `search` | string | No | Partial string match execution. |

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Operation successful",
    "timestamp": "2026-07-22T00:00:00Z",
    "data": {}
}
```

#### Common Error Codes
- `400 Bad Request`
- `401 Unauthorized` (Token invalid or expired)
- `403 Forbidden` (Insufficient permissions)
- `404 Not Found`
- `422 Unprocessable Entity` (Validation failure)
---

### <a name="endpoint-7"></a> POST `/api/v1/user/accounts/settings`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::accountSettings`

**Middleware / Guards:** `UserAuthMiddleware`

#### Request Body Example (JSON)
```json
{
    "example_field": "value"
}
```

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Operation successful",
    "timestamp": "2026-07-22T00:00:00Z",
    "data": {}
}
```

#### Common Error Codes
- `400 Bad Request`
- `401 Unauthorized` (Token invalid or expired)
- `403 Forbidden` (Insufficient permissions)
- `404 Not Found`
- `422 Unprocessable Entity` (Validation failure)
---

### <a name="endpoint-8"></a> GET `/api/v1/user/beneficiaries`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::beneficiary`

**Middleware / Guards:** `UserAuthMiddleware`

#### Query Parameters (Pagination/Search)
| Name | Type | Required | Description |
|---|---|---|---|
| `page` | integer | No | Default: 1. Target page for records. |
| `per_page` | integer | No | Default: 20. Max: 100. |
| `sort` | string | No | The field to sort results by. |
| `order` | string | No | `asc` or `desc`. |
| `search` | string | No | Partial string match execution. |

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Operation successful",
    "timestamp": "2026-07-22T00:00:00Z",
    "data": {}
}
```

#### Common Error Codes
- `400 Bad Request`
- `401 Unauthorized` (Token invalid or expired)
- `403 Forbidden` (Insufficient permissions)
- `404 Not Found`
- `422 Unprocessable Entity` (Validation failure)
---

### <a name="endpoint-9"></a> POST `/api/v1/user/beneficiaries`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::beneficiary`

**Middleware / Guards:** `UserAuthMiddleware`

#### Request Body Example (JSON)
```json
{
    "example_field": "value"
}
```

#### Response Example (200 OK)
```json
{
    "success": true,
    "status": 200,
    "message": "Operation successful",
    "timestamp": "2026-07-22T00:00:00Z",
    "data": {}
}
```

#### Common Error Codes
- `400 Bad Request`
- `401 Unauthorized` (Token invalid or expired)
- `403 Forbidden` (Insufficient permissions)
- `404 Not Found`
- `422 Unprocessable Entity` (Validation failure)
---

