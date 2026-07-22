---
title: "Architecture Patterns"
version: "1.0.0"
status: "Active"
owner: "Architecture Team"
category: "Software Documentation"
last_updated: "2026-07-22"
---

# Architecture Patterns

## MVC Pattern
- **Purpose**: Separates structural responsibilities.
- **Implementation**: Controllers mediate between Services and JSON Views.
- **Usage**: Used natively across the Presentation layer.

## Repository Pattern
- **Purpose**: Abstracts database I/O.
- **Implementation**: `App\Infrastructure\Repositories`.
- **Usage**: Isolates Domain Services from MySQL queries.

## Strategy Pattern
- **Purpose**: Hot-swapping algorithms.
- **Implementation**: Used in the authentication resolver (Token vs Session).

## Navigation
- **Parent Document**: [Software Architecture](../Software-Architecture.md)

## Cross References
- [Documentation Standards](../Documentation-Standards.md)

## Change Log
| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-22 | Architecture Team | Initial Phase 3 Generation |