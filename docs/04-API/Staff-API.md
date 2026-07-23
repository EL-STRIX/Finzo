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
   - [GET /Admin/staff-detail.php](#endpoint-0)
   - [POST /Admin/staff-action.php](#endpoint-1)
   - [GET /Pdf/staff-statement.php](#endpoint-2)
   - [GET /api/staff](#endpoint-3)
   - [POST /api/staff](#endpoint-4)
   - [GET /api/staff/suspended](#endpoint-5)
   - [GET /api/staff/next-id](#endpoint-6)
   - [GET /api/staff/{id}](#endpoint-7)
   - [PATCH /api/staff/{id}](#endpoint-8)
   - [POST /api/staff/{id}/password/generate](#endpoint-9)
   - [POST /api/staff/{id}/password/reset](#endpoint-10)
   - [POST /api/staff/{id}/photo](#endpoint-11)
   - [GET /api/documents/staff-statement](#endpoint-12)
   - [GET /api/v1/staff](#endpoint-13)
   - [POST /api/v1/staff](#endpoint-14)
   - [GET /api/v1/staff/suspended](#endpoint-15)
   - [GET /api/v1/staff/next-id](#endpoint-16)
   - [GET /api/v1/staff/{id}](#endpoint-17)
   - [PATCH /api/v1/staff/{id}](#endpoint-18)
   - [POST /api/v1/staff/{id}/password/generate](#endpoint-19)
   - [POST /api/v1/staff/{id}/password/reset](#endpoint-20)
   - [POST /api/v1/staff/{id}/photo](#endpoint-21)
   - [GET /api/v1/documents/staff-statement](#endpoint-22)


## Introduction
The `{$cleanName}` module encapsulates all operations related to {$cleanName} management within the Finzo Banking System. 
This document provides enterprise-grade specifications for frontend developers, integrators, and DevOps engineers.

## Authentication Requirements
Unless specified otherwise, endpoints in this module require a Bearer token or session cookie.
Role restrictions and strict Gate evaluations are enforced at the Controller boundary.

## API Endpoints

### <a name="endpoint-0"></a> GET `/Admin/staff-detail.php`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::staffDetail`

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

### <a name="endpoint-1"></a> POST `/Admin/staff-action.php`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::staffAction`

**Middleware / Guards:** `AdminAuthMiddleware`

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

### <a name="endpoint-2"></a> GET `/Pdf/staff-statement.php`

**Internal Handler:** `App\Presentation\Api\V1\Pdf\PdfController::staffStatement`

**Middleware / Guards:** ``

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

### <a name="endpoint-3"></a> GET `/api/staff`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::getStaffList`

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

### <a name="endpoint-4"></a> POST `/api/staff`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::addStaff`

**Middleware / Guards:** `AdminAuthMiddleware`

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

### <a name="endpoint-5"></a> GET `/api/staff/suspended`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::getSuspendedStaff`

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

### <a name="endpoint-6"></a> GET `/api/staff/next-id`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::getNextStaffId`

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

### <a name="endpoint-7"></a> GET `/api/staff/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::getStaffDetail`

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

### <a name="endpoint-8"></a> PATCH `/api/staff/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::staffAction`

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

### <a name="endpoint-9"></a> POST `/api/staff/{id}/password/generate`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::generateStaffPassword`

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

### <a name="endpoint-10"></a> POST `/api/staff/{id}/password/reset`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::resetStaffPassword`

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

### <a name="endpoint-11"></a> POST `/api/staff/{id}/photo`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::uploadStaffPhoto`

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

### <a name="endpoint-12"></a> GET `/api/documents/staff-statement`

**Internal Handler:** `App\Presentation\Api\V1\Pdf\PdfController::staffStatement`

**Middleware / Guards:** ``

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

### <a name="endpoint-13"></a> GET `/api/v1/staff`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::getStaffList`

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

### <a name="endpoint-14"></a> POST `/api/v1/staff`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::addStaff`

**Middleware / Guards:** `AdminAuthMiddleware`

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

### <a name="endpoint-15"></a> GET `/api/v1/staff/suspended`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::getSuspendedStaff`

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

### <a name="endpoint-16"></a> GET `/api/v1/staff/next-id`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::getNextStaffId`

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

### <a name="endpoint-17"></a> GET `/api/v1/staff/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::getStaffDetail`

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

### <a name="endpoint-18"></a> PATCH `/api/v1/staff/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::staffAction`

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

### <a name="endpoint-19"></a> POST `/api/v1/staff/{id}/password/generate`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::generateStaffPassword`

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

### <a name="endpoint-20"></a> POST `/api/v1/staff/{id}/password/reset`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::resetStaffPassword`

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

### <a name="endpoint-21"></a> POST `/api/v1/staff/{id}/photo`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminStaffController::uploadStaffPhoto`

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

### <a name="endpoint-22"></a> GET `/api/v1/documents/staff-statement`

**Internal Handler:** `App\Presentation\Api\V1\Pdf\PdfController::staffStatement`

**Middleware / Guards:** ``

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

