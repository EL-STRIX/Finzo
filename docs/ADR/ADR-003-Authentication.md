---
title: "ADR-003: Authentication & Session Decoupling"
status: "Accepted"
date: "2026-07-25"
deciders: ["Lead Backend Developer", "Enterprise Architect"]
---

# Architecture Decision Record 003: Authentication Decoupling

## Context
During Phase 17, the Authentication subsystem was heavily coupled to the PHP Session state. The `AuthService` directly manipulated `$_SESSION` superglobals upon successful credential validation. While functional for a traditional web application, this monolithic coupling strictly prevented the future integration of stateless APIs (JWT) and multi-factor authentication (MFA) workflows.

## Decision
We decided to strictly decouple credential validation from session hydration by enforcing the Clean Architecture boundaries.

1. **Domain Isolation:** `AuthService.php` is now purely responsible for cryptographic validation and business rules. It returns an `AuthenticationContext` array containing the user data and an `mfa_required` flag, but mutates exactly zero global state.
2. **Session Abstraction:** We introduced `SessionManager.php` as an infrastructure service responsible for HTTP-only cookie issuance, session ID regeneration, and timeout enforcement.
3. **Controller Orchestration:** The Presentation Layer (`AuthController`) acts as the middleman. It receives the validation payload from the Domain Service, checks if `mfa_required === true` (triggering a step-up challenge), and only if cleared, passes the payload to `SessionManager` to finalize the HTTP login.

## Consequences
- **Positive:** We achieved 100% readiness for Phase 18+ JWT integration because the `AuthService` can now be consumed by stateless API endpoints that do not utilize sessions.
- **Positive:** We eliminated a severe architectural anti-pattern (Domain logic touching HTTP globals).
- **Negative:** Increased initial complexity by introducing a dedicated Session Management layer instead of native PHP arrays.
