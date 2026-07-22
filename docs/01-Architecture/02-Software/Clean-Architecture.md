---
title: "Clean Architecture"
version: "1.0.0"
status: "Active"
owner: "Architecture Team"
category: "Software Documentation"
last_updated: "2026-07-22"
---

# Clean Architecture

## Application Layers
1. **Domain**: The core business objects.
2. **Application**: Use-case orchestration.
3. **Presentation**: Network boundaries.
4. **Infrastructure**: Disk and Network I/O.

## Dependency Rules
Dependencies MUST point inward. Infrastructure knows about the Domain. The Domain knows nothing about Infrastructure.

## Navigation
- **Parent Document**: [Software Architecture](../Software-Architecture.md)

## Cross References
- [Documentation Standards](../Documentation-Standards.md)

## Change Log
| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-22 | Architecture Team | Initial Phase 3 Generation |