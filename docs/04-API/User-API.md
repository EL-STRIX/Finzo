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
   - [GET /api/user/dashboard](#endpoint-0)
   - [GET /api/user/profile](#endpoint-1)
   - [POST /api/user/profile](#endpoint-2)
   - [POST /api/user/profile/complete](#endpoint-3)
   - [GET /api/user/notifications](#endpoint-4)
   - [POST /api/user/notifications](#endpoint-5)
   - [GET /api/user/statement](#endpoint-6)
   - [POST /api/user/security/change-password](#endpoint-7)
   - [POST /api/user/security/verify-pin](#endpoint-8)
   - [GET /api/v1/user/dashboard](#endpoint-9)
   - [GET /api/v1/user/profile](#endpoint-10)
   - [POST /api/v1/user/profile](#endpoint-11)
   - [POST /api/v1/user/profile/complete](#endpoint-12)
   - [GET /api/v1/user/notifications](#endpoint-13)
   - [POST /api/v1/user/notifications](#endpoint-14)
   - [GET /api/v1/user/statement](#endpoint-15)
   - [POST /api/v1/user/security/change-password](#endpoint-16)
   - [POST /api/v1/user/security/verify-pin](#endpoint-17)


## Introduction
The `{$cleanName}` module encapsulates all operations related to {$cleanName} management within the Finzo Banking System. 
This document provides enterprise-grade specifications for frontend developers, integrators, and DevOps engineers.

## Authentication Requirements
Unless specified otherwise, endpoints in this module require a Bearer token or session cookie.
Role restrictions and strict Gate evaluations are enforced at the Controller boundary.

## API Endpoints

### <a name="endpoint-0"></a> GET `/api/user/dashboard`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::dashboard`

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

### <a name="endpoint-1"></a> GET `/api/user/profile`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::profile`

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

### <a name="endpoint-2"></a> POST `/api/user/profile`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::profile`

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

### <a name="endpoint-3"></a> POST `/api/user/profile/complete`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::completeProfile`

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

### <a name="endpoint-4"></a> GET `/api/user/notifications`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::notifications`

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

### <a name="endpoint-5"></a> POST `/api/user/notifications`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::notifications`

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

### <a name="endpoint-6"></a> GET `/api/user/statement`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::statement`

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

### <a name="endpoint-7"></a> POST `/api/user/security/change-password`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::changePassword`

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

### <a name="endpoint-8"></a> POST `/api/user/security/verify-pin`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::verifyPin`

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

### <a name="endpoint-9"></a> GET `/api/v1/user/dashboard`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::dashboard`

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

### <a name="endpoint-10"></a> GET `/api/v1/user/profile`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::profile`

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

### <a name="endpoint-11"></a> POST `/api/v1/user/profile`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::profile`

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

### <a name="endpoint-12"></a> POST `/api/v1/user/profile/complete`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::completeProfile`

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

### <a name="endpoint-13"></a> GET `/api/v1/user/notifications`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::notifications`

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

### <a name="endpoint-14"></a> POST `/api/v1/user/notifications`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::notifications`

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

### <a name="endpoint-15"></a> GET `/api/v1/user/statement`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::statement`

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

### <a name="endpoint-16"></a> POST `/api/v1/user/security/change-password`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::changePassword`

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

### <a name="endpoint-17"></a> POST `/api/v1/user/security/verify-pin`

**Internal Handler:** `App\Presentation\Api\V1\User\UserController::verifyPin`

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

