---
title: "Software Architecture"
version: "1.0.0"
status: "Active"
owner: "Backend Engineering"
category: "Design Patterns"
last_updated: "2026-07-22"
---

# Software Architecture

## Table of Contents
1. [Architecture Pattern](#architecture-pattern)
2. [Clean Architecture Implementation](#clean-architecture-implementation)
3. [SOLID Principles](#solid-principles)
4. [Folder Structure](#folder-structure)
5. [Exception Handling](#exception-handling)

## Architecture Pattern
The platform operates on a rigid interpretation of **Clean Architecture** (originating from Uncle Bob), heavily favoring inversion of control and separation of concerns.

```mermaid
graph TD
    subgraph Presentation Layer
        C[Controllers]
        M[Middleware]
    end
    subgraph Application Layer
        S[Services]
        V[Validators / FormRequests]
    end
    subgraph Infrastructure Layer
        R[Repositories]
        D[Database Builders]
    end
    
    C --> V
    V --> S
    S --> R
    R --> D
```

## Clean Architecture Implementation
- **Presentation**: Translates HTTP semantics to Domain concepts.
- **Application**: The core banking logic. It remains entirely agnostic to HTTP or HTML.
- **Infrastructure**: Handles disk I/O, DB interactions, and caching.

## SOLID Principles
- **SRP**: E.g., `ApiQueryBuilder` handles *only* dynamic SQL construction.
- **OCP**: Middleware pipelines are dynamically extensible without modifying the `Router`.
- **DIP**: High-level services inject repositories rather than instantiating them directly.

## Folder Structure
```text
app/
├── Application/       # Logic (Services, Requests, DTOs)
├── Domain/            # Core models and entities
├── Infrastructure/    # Database, Repositories
├── Presentation/      # Controllers, Middleware, APIs
└── Shared/            # Core utilities (Cache, Router, Docs)
```

## Exception Handling
All exceptions bubble up to a unified `ExceptionHandler` located in `App\Shared\Core\Exceptions`. Raw stack traces are strictly prohibited from entering HTTP response streams.