---
title: "API Architecture"
version: "1.0.0"
status: "Active"
owner: "Architecture Team"
category: "API Design"
last_updated: "2026-07-22"
---

# API Architecture

## Table of Contents
1. [REST Principles](#rest-principles)
2. [Versioning](#versioning)
3. [Pagination and Queries](#pagination-and-queries)
4. [Security & Idempotency](#security--idempotency)
5. [Cross References](#cross-references)

## REST Principles
The Finzo API leverages explicit HTTP verb assignments:
- `GET`: Read-only resource retrieval.
- `POST`: Resource creation or complex state mutators.
- `PUT/PATCH`: Idempotent resource updates.
- `DELETE`: Logical soft deletion.

## Versioning
All enterprise routes are strictly prefixed with `/api/v1/`. Breaking changes mandate the instantiation of a concurrent `/api/v2/` namespace to protect existing consumer integrations.

## Pagination and Queries
Collections defer to the `ApiQueryBuilder`, standardizing query parameter parsing:
- `?page=1&per_page=20`
- `?sort=created_at&order=desc`
- `?search=term`
The engine guarantees metadata injection (`total`, `has_next`, `last_page`) on all list responses.

## Security & Idempotency
- CSRF validation is disabled for API routes as authentication is strictly token/session bounded with origin verification.
- CORS headers restrict access strictly to whitelisted domains in the environment configuration.

## Cross References
- [API Documentation Suite](../04-API/README.md)
## Navigation
- **Parent Document**: [Overview.md](Overview.md)
- **Child Documents**: See sub-folders.
- **Previous Document**: N/A
- **Next Document**: N/A
- **Related Documents**: [Documentation Standards](Documentation-Standards.md)

## Change Log
| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-22 | Architecture Team | Initial Phase 1 Setup |
