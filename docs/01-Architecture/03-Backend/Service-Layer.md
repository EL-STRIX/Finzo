---
title: "Service Layer"
version: "1.0.0"
status: "Active"
owner: "Backend Engineering"
category: "Backend Subsystem"
last_updated: "2026-07-22"
---

# Service Layer

## Service Layer
**Purpose**: The absolute truth of business logic.
**Dependencies Allowed**: Repositories, other Services, Event Dispatchers.
**Dependencies Forbidden**: Controllers, `$_POST`, HTML.
**Lifecycle**: Injected into Controllers, executes transactional logic.

## Navigation
- **Parent Document**: [Backend Architecture](../Backend-Architecture.md)

## Change Log
| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-22 | Backend Team | Initial Phase 5 Generation |