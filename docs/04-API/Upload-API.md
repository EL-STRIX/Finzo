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
   - [POST /api/uploads/branch-cert](#endpoint-0)
   - [POST /api/uploads/id-document](#endpoint-1)
   - [POST /api/v1/uploads/branch-cert](#endpoint-2)
   - [POST /api/v1/uploads/id-document](#endpoint-3)


## Introduction
The `{$cleanName}` module encapsulates all operations related to {$cleanName} management within the Finzo Banking System. 
This document provides enterprise-grade specifications for frontend developers, integrators, and DevOps engineers.

## Authentication Requirements
Unless specified otherwise, endpoints in this module require a Bearer token or session cookie.
Role restrictions and strict Gate evaluations are enforced at the Controller boundary.

## API Endpoints

### <a name="endpoint-0"></a> POST `/api/uploads/branch-cert`

**Internal Handler:** `UploadController::uploadBranchCert`

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

### <a name="endpoint-1"></a> POST `/api/uploads/id-document`

**Internal Handler:** `UploadController::uploadIdDocument`

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

### <a name="endpoint-2"></a> POST `/api/v1/uploads/branch-cert`

**Internal Handler:** `UploadController::uploadBranchCert`

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

### <a name="endpoint-3"></a> POST `/api/v1/uploads/id-document`

**Internal Handler:** `UploadController::uploadIdDocument`

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

