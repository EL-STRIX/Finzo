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
   - [POST /Admin/admin-action.php](#endpoint-0)
   - [GET /Admin/users.php](#endpoint-1)
   - [GET /api/admins](#endpoint-2)
   - [POST /api/admins](#endpoint-3)
   - [GET /api/admins/locked](#endpoint-4)
   - [GET /api/admins/next-id](#endpoint-5)
   - [GET /api/admins/profile](#endpoint-6)
   - [POST /api/admins/profile](#endpoint-7)
   - [POST /api/admins/profile/photo](#endpoint-8)
   - [GET /api/admins/{id}](#endpoint-9)
   - [PATCH /api/admins/{id}](#endpoint-10)
   - [GET /api/admin/dashboard](#endpoint-11)
   - [GET /api/admin/audit-logs](#endpoint-12)
   - [GET /api/admin/interest](#endpoint-13)
   - [POST /api/admin/interest](#endpoint-14)
   - [GET /api/users](#endpoint-15)
   - [GET /api/users/suspended](#endpoint-16)
   - [GET /api/users/deleted](#endpoint-17)
   - [GET /api/users/locked](#endpoint-18)
   - [GET /api/users/locked/{id}](#endpoint-19)
   - [POST /api/users/locked/{id}/unlock](#endpoint-20)
   - [GET /api/users/pending](#endpoint-21)
   - [GET /api/users/{id}](#endpoint-22)
   - [PATCH /api/users/{id}](#endpoint-23)
   - [POST /api/users/{id}/recover](#endpoint-24)
   - [GET /api/v1/admins](#endpoint-25)
   - [POST /api/v1/admins](#endpoint-26)
   - [GET /api/v1/admins/locked](#endpoint-27)
   - [GET /api/v1/admins/next-id](#endpoint-28)
   - [GET /api/v1/admins/profile](#endpoint-29)
   - [POST /api/v1/admins/profile](#endpoint-30)
   - [POST /api/v1/admins/profile/photo](#endpoint-31)
   - [GET /api/v1/admins/{id}](#endpoint-32)
   - [PATCH /api/v1/admins/{id}](#endpoint-33)
   - [GET /api/v1/admin/dashboard](#endpoint-34)
   - [GET /api/v1/admin/audit-logs](#endpoint-35)
   - [GET /api/v1/admin/interest](#endpoint-36)
   - [POST /api/v1/admin/interest](#endpoint-37)
   - [GET /api/v1/users](#endpoint-38)
   - [GET /api/v1/users/suspended](#endpoint-39)
   - [GET /api/v1/users/deleted](#endpoint-40)
   - [GET /api/v1/users/locked](#endpoint-41)
   - [GET /api/v1/users/locked/{id}](#endpoint-42)
   - [POST /api/v1/users/locked/{id}/unlock](#endpoint-43)
   - [GET /api/v1/users/pending](#endpoint-44)
   - [GET /api/v1/users/{id}](#endpoint-45)
   - [PATCH /api/v1/users/{id}](#endpoint-46)
   - [POST /api/v1/users/{id}/recover](#endpoint-47)


## Introduction
The `{$cleanName}` module encapsulates all operations related to {$cleanName} management within the Finzo Banking System. 
This document provides enterprise-grade specifications for frontend developers, integrators, and DevOps engineers.

## Authentication Requirements
Unless specified otherwise, endpoints in this module require a Bearer token or session cookie.
Role restrictions and strict Gate evaluations are enforced at the Controller boundary.

## API Endpoints

### <a name="endpoint-0"></a> POST `/Admin/admin-action.php`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::adminAction`

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

### <a name="endpoint-1"></a> GET `/Admin/users.php`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::users`

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

### <a name="endpoint-2"></a> GET `/api/admins`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::getAdmins`

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

### <a name="endpoint-3"></a> POST `/api/admins`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::addAdmin`

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

### <a name="endpoint-4"></a> GET `/api/admins/locked`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::getLockedAdmins`

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

### <a name="endpoint-5"></a> GET `/api/admins/next-id`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::getNextAdminId`

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

### <a name="endpoint-6"></a> GET `/api/admins/profile`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::getAdminProfile`

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

### <a name="endpoint-7"></a> POST `/api/admins/profile`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::updateAdminProfile`

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

### <a name="endpoint-8"></a> POST `/api/admins/profile/photo`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::uploadAdminPhoto`

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

### <a name="endpoint-9"></a> GET `/api/admins/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::getAdminDetail`

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

### <a name="endpoint-10"></a> PATCH `/api/admins/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::adminAction`

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

### <a name="endpoint-11"></a> GET `/api/admin/dashboard`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::dashboard`

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

### <a name="endpoint-12"></a> GET `/api/admin/audit-logs`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::auditLog`

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

### <a name="endpoint-13"></a> GET `/api/admin/interest`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::interest`

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

### <a name="endpoint-14"></a> POST `/api/admin/interest`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::interest`

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

### <a name="endpoint-15"></a> GET `/api/users`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::users`

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

### <a name="endpoint-16"></a> GET `/api/users/suspended`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::suspendedUsers`

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

### <a name="endpoint-17"></a> GET `/api/users/deleted`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::deletedUsers`

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

### <a name="endpoint-18"></a> GET `/api/users/locked`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::lockedAccounts`

**Middleware / Guards:** `AdminAuthMiddleware`

#### Query Parameters (Pagination/Search)
| Name | Type | Required | Description |
|---|---|---|---|
| `page` | integer | No | Default: 1. Target page for records. |
| `per_page` | integer | No | Default: 20. Min: 10. Max: 50. |
| `tab` | string | No | `locked` (default) or `flagged` (3+ failed attempts, not yet locked). |
| `search` | string | No | Matches full name, email, phone or account number. |
| `type` | string | No | `Savings` or `Current`. Filters by account type. |
| `status` | string | No | `pending` (temporary password not yet generated) or `done` (generated). |

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

### <a name="endpoint-19"></a> GET `/api/users/locked/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::lockedAccountDetail`

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

### <a name="endpoint-20"></a> POST `/api/users/locked/{id}/unlock`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::unlockAccount`

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

### <a name="endpoint-21"></a> GET `/api/users/pending`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::pendingApprovals`

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

### <a name="endpoint-22"></a> GET `/api/users/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::userDetail`

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

### <a name="endpoint-23"></a> PATCH `/api/users/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::userAction`

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

### <a name="endpoint-24"></a> POST `/api/users/{id}/recover`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::recoverUser`

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

### <a name="endpoint-25"></a> GET `/api/v1/admins`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::getAdmins`

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

### <a name="endpoint-26"></a> POST `/api/v1/admins`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::addAdmin`

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

### <a name="endpoint-27"></a> GET `/api/v1/admins/locked`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::getLockedAdmins`

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

### <a name="endpoint-28"></a> GET `/api/v1/admins/next-id`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::getNextAdminId`

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

### <a name="endpoint-29"></a> GET `/api/v1/admins/profile`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::getAdminProfile`

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

### <a name="endpoint-30"></a> POST `/api/v1/admins/profile`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::updateAdminProfile`

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

### <a name="endpoint-31"></a> POST `/api/v1/admins/profile/photo`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::uploadAdminPhoto`

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

### <a name="endpoint-32"></a> GET `/api/v1/admins/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::getAdminDetail`

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

### <a name="endpoint-33"></a> PATCH `/api/v1/admins/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminManagementController::adminAction`

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

### <a name="endpoint-34"></a> GET `/api/v1/admin/dashboard`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::dashboard`

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

### <a name="endpoint-35"></a> GET `/api/v1/admin/audit-logs`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::auditLog`

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

### <a name="endpoint-36"></a> GET `/api/v1/admin/interest`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::interest`

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

### <a name="endpoint-37"></a> POST `/api/v1/admin/interest`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::interest`

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

### <a name="endpoint-38"></a> GET `/api/v1/users`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::users`

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

### <a name="endpoint-39"></a> GET `/api/v1/users/suspended`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::suspendedUsers`

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

### <a name="endpoint-40"></a> GET `/api/v1/users/deleted`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::deletedUsers`

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

### <a name="endpoint-41"></a> GET `/api/v1/users/locked`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::lockedAccounts`

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

### <a name="endpoint-42"></a> GET `/api/v1/users/locked/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::lockedAccountDetail`

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

### <a name="endpoint-43"></a> POST `/api/v1/users/locked/{id}/unlock`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::unlockAccount`

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

### <a name="endpoint-44"></a> GET `/api/v1/users/pending`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::pendingApprovals`

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

### <a name="endpoint-45"></a> GET `/api/v1/users/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::userDetail`

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

### <a name="endpoint-46"></a> PATCH `/api/v1/users/{id}`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::userAction`

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

### <a name="endpoint-47"></a> POST `/api/v1/users/{id}/recover`

**Internal Handler:** `App\Presentation\Api\V1\Admin\AdminOperationsController::recoverUser`

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

