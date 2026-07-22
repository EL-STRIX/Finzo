---
title: "ERD"
version: "1.0.0"
status: "Active"
owner: "Database Administration"
category: "Database Subsystem"
last_updated: "2026-07-22"
---

# ERD

## Entity Relationship Diagram
```mermaid
erDiagram
    USERS ||--o{ ACCOUNTS : owns
    ACCOUNTS ||--o{ TRANSACTIONS : has
    BRANCHES ||--o{ USERS : manages
```

## Navigation
- **Parent Document**: [Database Architecture](../Database-Architecture.md)

## Change Log
| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-22 | DB Team | Initial Phase 6 Generation |