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
   - [GET /Admin/view-kyc-document.php](#endpoint-0)
   - [GET /api/admin/applications/{id}](#endpoint-1)
   - [POST /api/admin/applications/{id}/review](#endpoint-2)
   - [GET /api/users/{id}/kyc](#endpoint-3)
   - [GET /api/v1/admin/applications/{id}](#endpoint-4)
   - [POST /api/v1/admin/applications/{id}/review](#endpoint-5)
   - [GET /api/v1/users/{id}/kyc](#endpoint-6)


## Introduction
The `{$cleanName}` module encapsulates all operations related to {$cleanName} management within the Finzo Banking System. 
This document provides enterprise-grade specifications for frontend developers, integrators, and DevOps engineers.

## Authentication Requirements
Unless specified otherwise, endpoints in this module require a Bearer token or session cookie.
Role restrictions and strict Gate evaluations are enforced at the Controller boundary.

## API Endpoints

### <a name="endpoint-0"></a> GET `/Admin/view-kyc-document.php`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::viewKycDocument`

**Middleware / Guards:** `AdminAuthMiddleware`

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

### <a name="endpoint-1"></a> GET `/api/admin/applications/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::reviewApplication`

**Middleware / Guards:** `AdminAuthMiddleware`

#### Path Parameters
| Name | Type | Required | Description |
|---|---|---|---|
| `id` | string | Yes | The target identifier for the resource. |

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

### <a name="endpoint-2"></a> POST `/api/admin/applications/{id}/review`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::reviewApplication`

**Middleware / Guards:** `AdminAuthMiddleware`

#### Path Parameters
| Name | Type | Required | Description |
|---|---|---|---|
| `id` | string | Yes | The target identifier for the resource. |

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

### <a name="endpoint-3"></a> GET `/api/users/{id}/kyc`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::viewKycDocument`

**Middleware / Guards:** `AdminAuthMiddleware`

#### Path Parameters
| Name | Type | Required | Description |
|---|---|---|---|
| `id` | string | Yes | The target identifier for the resource. |

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

### <a name="endpoint-4"></a> GET `/api/v1/admin/applications/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::reviewApplication`

**Middleware / Guards:** `AdminAuthMiddleware`

#### Path Parameters
| Name | Type | Required | Description |
|---|---|---|---|
| `id` | string | Yes | The target identifier for the resource. |

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

### <a name="endpoint-5"></a> POST `/api/v1/admin/applications/{id}/review`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::reviewApplication`

**Middleware / Guards:** `AdminAuthMiddleware`

#### Path Parameters
| Name | Type | Required | Description |
|---|---|---|---|
| `id` | string | Yes | The target identifier for the resource. |

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

### <a name="endpoint-6"></a> GET `/api/v1/users/{id}/kyc`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::viewKycDocument`

**Middleware / Guards:** `AdminAuthMiddleware`

#### Path Parameters
| Name | Type | Required | Description |
|---|---|---|---|
| `id` | string | Yes | The target identifier for the resource. |

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

