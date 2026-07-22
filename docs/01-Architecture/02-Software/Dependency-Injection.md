---
title: "Dependency Injection"
version: "1.0.0"
status: "Active"
owner: "Architecture Team"
category: "Software Documentation"
last_updated: "2026-07-22"
---

# Dependency Injection

## Constructor Injection
Finzo strictly enforces constructor injection. 

```php
public function __construct(UserRepository $userRepo) {
    $this->userRepo = $userRepo;
}
```

## Benefits
Guarantees that a class cannot be instantiated in an invalid state. Promotes massive testability.

## Navigation
- **Parent Document**: [Software Architecture](../Software-Architecture.md)

## Cross References
- [Documentation Standards](../Documentation-Standards.md)

## Change Log
| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-22 | Architecture Team | Initial Phase 3 Generation |