---
title: "Global Error Codes"
version: "1.0.0"
category: "Core Specification"
owner: "Architecture Team"
last_updated: "2026-07-22"
---

# Global Error Codes

## Introduction
The API centralizes all exception handling via the `ExceptionHandler`. This guarantees that internal stack traces are never exposed in production.

## Standard Payload
```json
{
    "success": false,
    "status": 422,
    "message": "Validation failed",
    "errors": {
         "email": ["The email format is invalid"]
    }
}
```

## HTTP Status Mapping
- **200/201**: Success / Created
- **400**: Malformed Syntax
- **401**: Missing or Invalid Bearer Token
- **403**: RBAC / Gate execution blocked
- **404**: Resource missing
- **422**: FormRequest Validation failure
- **429**: Rate Limiter threshold breached
- **500**: Unhandled server exception

