---
title: "API Architecture"
version: "1.0.0"
status: "Active"
owner: "API Gateway Team"
category: "Network Integration"
last_updated: "2026-07-22"
---

# API Architecture

## Table of Contents
1. [Executive Summary](#executive-summary)
2. [API Overview](#api-overview)
3. [Purpose](#purpose)
4. [Scope](#scope)
5. [API Design Philosophy](#api-design-philosophy)
6. [REST Architecture](#rest-architecture)
7. [Endpoint Organization](#endpoint-organization)
8. [Route Structure](#route-structure)
9. [Request Lifecycle](#request-lifecycle)
10. [Response Lifecycle](#response-lifecycle)
11. [Authentication](#authentication)
12. [Authorization](#authorization)
13. [Session Management](#session-management)
14. [Validation](#validation)
15. [Middleware](#middleware)
16. [Error Handling](#error-handling)
17. [Exception Handling](#exception-handling)
18. [Response Format](#response-format)
19. [HTTP Status Codes](#http-status-codes)
20. [Pagination](#pagination)
21. [Filtering](#filtering)
22. [Sorting](#sorting)
23. [Searching](#searching)
24. [Rate Limiting](#rate-limiting)
25. [Security](#security)
26. [Performance](#performance)
27. [Versioning](#versioning)
28. [Logging](#logging)
29. [Monitoring](#monitoring)
30. [API Testing](#api-testing)
31. [Best Practices](#best-practices)
32. [Anti-Patterns](#anti-patterns)
33. [Future Improvements](#future-improvements)
34. [Cross References](#cross-references)
35. [References](#references)
36. [Change Log](#change-log)

## Executive Summary
The Finzo API acts as the universal boundary between the protected financial ledger and the hostile public internet. Operating on strict RESTful principles, the API enforces Zero-Trust authentication and guarantees standardized JSON payloads across all responses.

## API Overview
This is a Level 2 REST API (per the Richardson Maturity Model), utilizing explicit HTTP verbs (`GET`, `POST`, `PUT`, `DELETE`) mapped directly to resource mutations. It serves the primary web dashboard, the mobile application, and authorized third-party integrations.

## Purpose
This document provides integrators, backend developers, and QA engineers with the exact specifications for consuming, mocking, and altering the Finzo API ecosystem safely.

## Scope
The scope covers URL topologies, standard header requirements, JSON schema definitions for requests/responses, rate-limiting constraints, and explicit documentation for the Request/Response middleware lifecycle.

## API Design Philosophy
**Predictability over Flexibility.** An API consumer should be able to guess the URL of an endpoint based on the resource name. Errors must be painfully explicit.

## REST Architecture
Resources are modeled as nouns. 
- `GET /users` (Collection)
- `GET /users/123` (Singleton)
- `POST /users/123/accounts` (Sub-collection creation)

## Endpoint Organization
Endpoints are partitioned by operational role (Admin, Staff, User) at the namespace level to guarantee isolated Controller routing and simplify RBAC middleware targeting.

## Route Structure
Routes exist in `routes/api.php` and map specific URIs to fully qualified Controller class names.

## Request Lifecycle
1. Nginx terminates TLS.
2. PHP handles the TCP packet.
3. Router matches `/api/v1/transfer` to `TransferController`.
4. Middleware validates Bearer token.
5. FormRequest sanitizes payload.

## Response Lifecycle
1. Controller receives `TransferEntity`.
2. Controller maps Entity to Array.
3. `ResponseBuilder::json()` constructs standard `{ success, message, data }`.
4. PHP emits `Content-Type: application/json`.

## Authentication
Every request to `/api/v1/*` (except `/auth/login`) requires an `Authorization: Bearer <token>` header.

## Authorization
Tokens contain encoded claims. Middleware inspects the `role` claim to grant or deny access to specific sub-namespaces.

## Session Management
Sessions are entirely stateless from the API's perspective. The token dictates the session. 

## Validation
Validation explicitly rejects unexpected fields. If a client submits `{"amount": 500, "is_admin": true}`, the validation layer strips `is_admin` completely before it reaches the Controller.

## Middleware
Middleware acts as an onion. Requests penetrate through CORS, Rate Limiting, and Auth before reaching the core logic.

## Error Handling
The API never returns HTML error pages. Even 404s and 500s are captured by the Global Exception Handler and formatted as JSON.

## Exception Handling
Exceptions map directly to HTTP codes: `ValidationException` -> 422, `DomainException` -> 400.

## Response Format
```json
{
  "success": true,
  "message": "Transfer successful",
  "data": {
    "transaction_id": "txn_89123"
  }
}
```

## HTTP Status Codes
- `200 OK`: Successful read/update.
- `201 Created`: Successful creation.
- `400 Bad Request`: Business logic failure.
- `401 Unauthorized`: Invalid Token.
- `403 Forbidden`: Insufficient permissions.
- `422 Unprocessable Entity`: Form validation failed.
- `429 Too Many Requests`: Rate limit exceeded.
- `500 Internal Server Error`: Infrastructure failure.

## Pagination
Large collections must use `?page=1&limit=20`. Responses include a `meta` block detailing `total`, `current_page`, and `last_page`.

## Filtering
Filters are applied via query parameters: `?status=pending&currency=USD`.

## Sorting
Sorting utilizes the `sort` parameter: `?sort=-created_at` (descending) or `?sort=amount` (ascending).

## Searching
Global text searches utilize `?q=John+Doe`.

## Rate Limiting
Enforced strictly per IP and per Bearer token using the Token Bucket algorithm (Atomic FileCache).

## Security
- Idempotency Keys to prevent double-charging.
- Helmet-style security headers (`X-Content-Type-Options: nosniff`).

## Performance
GZIP compression enabled for all JSON responses exceeding 1KB.

## Versioning
Versioned in the URL path: `/api/v1/`.

## Logging
Every HTTP 4xx and 5xx response is logged with the Request ID for debugging.

## Monitoring
APM headers inject `X-Response-Time` directly into the payload.

## API Testing
Tested extensively via PHPUnit Feature tests simulating exact HTTP JSON payloads.

## Best Practices
Always validate data. Never trust the client.

## Anti-Patterns
Returning `200 OK` with a `{ success: false }` payload. The HTTP status code MUST match the logical outcome.


### Deep Dive: Advanced Rate Limiting and DDOS Protection
A financial API is a primary target for volumetric DDOS and brute-force credential stuffing.
- **Global Ingress Limiting**: Nginx restricts connections to 100/sec per IP before PHP is ever invoked.
- **Application Limiting**: The `RateLimitMiddleware` restricts authenticated users to 60 requests per minute.
- **Brute-Force Protection**: The `/api/v1/auth/login` endpoint is heavily throttled to 5 attempts per 15 minutes per IP.
- **Penalty Box**: Repeatedly hitting rate limits automatically bans the IP at the firewall (UFW) level for 24 hours.

### Idempotency Mechanics in Detail
When executing a POST request to `/api/v1/transactions/transfer`, network unreliability may cause the client to drop the connection before receiving the `201 Created` response. The client might blindly retry the request, resulting in a duplicate transfer.
- **The Fix**: The client generates a unique UUIDv4 and includes it in the `Idempotency-Key` header.
- The Middleware intercepts this key and checks the Atomic Cache.
- If the key exists, the original cached JSON response is returned immediately.
- If the key does not exist, the request proceeds, and the generated response is cached against the Idempotency Key for 24 hours.

### API Gateway Integration
As Finzo scales toward a microservices topology, the PHP Router will be abstracted behind a dedicated API Gateway (e.g., Kong, Tyk, or AWS API Gateway).
- The Gateway will handle TLS termination, JWT validation, and Rate Limiting.
- The PHP backend will simply trust the `X-User-Id` header injected by the API Gateway.
- Currently, this logic remains explicitly coded in PHP Middleware to support monolithic deployments, but the architecture strictly decouples the Auth checks to make this future transition seamless.

### Standardized Error Payloads (RFC 7807)
While Finzo uses a custom JSON wrapper (`success`, `message`), complex validation errors closely mirror RFC 7807 Problem Details.
```json
{
  "success": false,
  "message": "Validation Failed",
  "errors": {
    "amount": ["The amount must be a positive integer."],
    "currency": ["The currency field must be exactly 3 characters."]
  }
}
```
This guarantees the frontend has exact granular details to highlight specific HTML input fields in red.

### Webhook Dispatch Architecture
The API does not only consume requests; it dispatches them.
- When an enterprise account receives a deposit, a Webhook must be fired to their ERP system.
- Webhooks are NEVER dispatched synchronously in the HTTP controller.
- A `WebhookDispatchJob` is pushed to the Queue. The background worker attempts the POST request, handling timeouts, retries, and cryptographic payload signing (HMAC-SHA256) independently of the user's API response time.


### Advanced Response Formatting Strategy
The API enforces a strict envelope pattern for all responses, bypassing the unpredictable structures often returned by monolithic frameworks.
- **Envelope Rationale**: By wrapping all responses in a `success`, `message`, and `data` envelope, frontend developers never have to guess if the payload is an Array or an Object. 
- **Error Envelopes**: Errors append an `errors` key containing granular field-level validation messages. The HTTP status code (e.g., 422) always matches the envelope state (`success: false`).

### The Token Lifecycle and Revocation
Bearer tokens (JWTs) are inherently stateless, presenting a challenge for immediate revocation (e.g., if a user's account is compromised).
- Finzo handles this by checking a `token_blacklist` table in the Redis cache during the Middleware validation phase.
- If an admin clicks "Revoke Sessions", the user's ID is written to the blacklist. The API middleware intercepts all subsequent requests possessing that User ID in the JWT payload and rejects them with a `401 Unauthorized` until the JWT naturally expires, balancing stateless performance with stateful security overrides.

### Zero-Trust Internal API Security
Even if an API request originates from within the Finzo private subnet (e.g., a background worker calling a system endpoint), it is treated as hostile.
- There are no IP whitelists that bypass the Authorization middleware.
- Internal workers must authenticate using a dedicated `Service Account` JWT with explicit, severely limited claims (e.g., `role: internal_worker`, `scope: write_notifications`).

### Multi-Region Latency Budgets
To achieve global sub-100ms response times, Finzo enforces a strict "Latency Budget" per API route.
- An endpoint is not allowed to execute more than 3 distinct SQL queries.
- Any operation requiring heavy aggregation (e.g., generating a user's full 12-month transaction history) must execute against the Read Replica. If the Read Replica latency exceeds 50ms, the API immediately falls back to returning the cached snapshot from Redis.

## Future Improvements
Implement OpenAPI/Swagger automated generation from DocBlocks.

## Cross References
- [Backend Architecture](../Backend-Architecture.md)

## References
- RESTful Web APIs (Leonard Richardson)
- JSON:API Standard

## Change Log
| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-22 | API Team | Phase 7 API Architecture |

### Webhook Signature Verification (HMAC)
When Finzo dispatches a webhook to a third-party consumer, it is critical that the consumer can verify the payload originated from Finzo and was not intercepted/tampered with in transit.
- Every webhook request includes an X-Finzo-Signature header.
- This signature is an HMAC-SHA256 hash of the raw JSON request body, signed using the consumer's unique webhook_secret.
- Consumers must compute the hash on their end and use a timing-safe string comparison function (e.g., hash_equals in PHP) to verify the signature matches.

### API Gateway Rate Limit Headers
When rate limits are applied, the API Gateway (or PHP Middleware) injects standardized headers to assist client-side rate limit management.
- X-RateLimit-Limit: The maximum number of requests permitted in the current time window.
- X-RateLimit-Remaining: The number of requests remaining.
- X-RateLimit-Reset: The UNIX timestamp indicating when the bucket will refill.

### Secure File Uploads via the API
File uploads (e.g., for KYC identity verification) cannot be handled as standard JSON.
- The API accepts multipart/form-data for the /api/v1/kyc/upload endpoint.
- Before the file is moved to persistent storage, the backend rigorously parses the file's MIME type using info (not relying on the client-provided extension).
- Exif data is stripped from JPEGs using ImageMagick to remove GPS tracking data before storage.
- The API responds with a temporary UUID referencing the document, rather than the raw S3 URL, preventing direct object access.

### GraphQL Considerations
While the current architecture is strictly RESTful, heavy administrative dashboards often suffer from "Over-fetching" when utilizing standard REST endpoints.
- If GraphQL is introduced, it will sit securely *behind* the existing Authorization middleware.
- A single endpoint /api/graphql will accept POST requests.
- The GraphQL resolvers will map directly to the existing Service layer, guaranteeing that business logic and database constraints remain identical regardless of whether the entry point was REST or GraphQL.


### API Telemetry and Distributed Tracing
When a request passes through the API, it may touch the database, Redis, and a 3rd party service (like an SMS provider).
- The API middleware generates a unique X-Trace-ID (UUIDv4) upon request ingress.
- This Trace ID is appended to every internal log entry, every database query log, and every downstream HTTP request.
- This allows DevOps to stitch together a comprehensive timeline of the request lifecycle in Datadog or Zipkin, pinpointing exactly which subsystem caused a 200ms latency spike.

### Client SDK Generation
To ensure frontend and third-party consumers can seamlessly integrate with the Finzo API:
- The backend leverages strictly formatted DocBlocks and PHP Attributes to describe all endpoints.
- During the CI/CD pipeline, an automated process (e.g., Swagger-PHP) generates an openapi.yaml specification file.
- This spec file is ingested by OpenAPI Generator to compile strictly-typed client libraries (SDKs) in TypeScript, Swift (for iOS), and Kotlin (for Android).
- This guarantees that the client-side code is always perfectly synchronized with the server's expected validation rules, drastically reducing integration bugs.


### Content Negotiation and Payload Compression
To ensure rapid payload delivery over mobile networks (3G/4G):
- The API respects the Accept-Encoding: gzip, deflate, br header provided by the client.
- Nginx automatically compresses all JSON responses using Brotli (if supported) or Gzip, reducing the payload size of a 100KB transaction history array down to approximately 15KB.
- The API explicitly returns Vary: Accept-Encoding to ensure intermediary caches (like Cloudflare) store and serve the correct compressed version of the payload based on the client's capabilities.

### Error Monitoring Integration (Sentry)
Silent failures in a REST API are catastrophic.
- The Global Exception Handler is deeply integrated with an error monitoring platform (like Sentry or Bugsnag).
- Whenever an unexpected 500 Internal Server Error is triggered (e.g., a null pointer exception in a Service class), the full stack trace, the incoming HTTP headers, and the sanitized request payload are asynchronously shipped to Sentry.
- PII (Passwords, Bearer Tokens, Credit Card Numbers) are explicitly scrubbed from the payload *before* transmission to Sentry using a strictly defined regex dictionary.


### Asynchronous Client Webhooks (Retries and Backoff)
When Finzo acts as the client, pushing data to external integrations (e.g., triggering a background check via an external KYC provider), it must handle remote unreliability gracefully.
- The WebhookClientService does not execute requests synchronously.
- It pushes a DispatchExternalWebhookJob to the Redis queue.
- If the external provider returns a 503 Service Unavailable or a 429 Too Many Requests, the worker intercepts the failure.
- It utilizes an **Exponential Backoff Algorithm** (e.g., retrying in 1 minute, then 2 minutes, then 4 minutes, then 8 minutes) up to a maximum of 5 attempts before marking the job as permanently failed. This prevents Finzo from accidentally participating in a DDOS attack against a struggling partner API.

### Deprecation Strategy
APIs are contracts. Breaking a contract breaks the client.
- When an endpoint is marked for deprecation (e.g., transitioning from /api/v1/user/settings to /api/v2/user/preferences), the original 1 endpoint is NOT immediately removed.
- Instead, the backend begins injecting a Deprecation: @Date HTTP header into the response of the 1 endpoint.
- An alert is automatically fired to the engineering team if a deprecated endpoint receives more than 100 requests a day, signaling that client migrations are lagging. Only after 6 months of metrics showing near-zero utilization is the code physically removed from the router.

