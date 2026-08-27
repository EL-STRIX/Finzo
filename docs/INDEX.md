# Finzo Enterprise Platform Documentation Index

> Enterprise Banking System — Technical Documentation for Developers, DBAs, Security Engineers, and Auditors.

---

## 1. Master Reports & Case Studies

| Document | Description |
|---|---|
| [Project Report](reports/PROJECT_REPORT.md) | Comprehensive architecture, auth flows, and engineering specifications |
| [Engineering Case Study](case-study/FINZO_CASE_STUDY.md) | In-depth architectural case study and design rationale |
| [Accessibility Audit](reports/Accessibility-Audit.md) | Formal WCAG 2.2 AA audit and verification |
| [Security Audit](reports/Security-Audit.md) | Security architecture, threat modeling, and defense mechanisms |
| [UI Audit](reports/UI-Audit.md) | UI standards, token consistency, and responsive audits |
| [UX Audit](reports/UX-Audit.md) | User experience flow and interaction audits |

---

## 2. Architecture & Standards

| Document | Description |
|---|---|
| [Architecture Overview](ARCHITECTURE.md) | Clean architecture, design patterns, and request lifecycle |
| [Design System](design-system/FINZO_DESIGN_SYSTEM.md) | CSS tokens, component library, typography, and accessibility rules |
| [ADR-003: Auth Decoupling](ADR/ADR-003-Authentication.md) | Architecture Decision Record — session and auth service decoupling |

---

## 3. Database

| Document | Description |
|---|---|
| [Database Architecture](03-Database/Database-Architecture.md) | Database design philosophy, schema layers, and financial integrity |
| [Schema Reference](03-Database/Schema-Reference.md) | All tables, columns, constraints, and entity relationships |
| [Naming Conventions](03-Database/Naming-Convention.md) | Database and migration naming standards |
| [Migration Guide](03-Database/Migration-Guide.md) | DBA runbooks — bootstrap, deploy, rollback, and BCDR |
| [Security Guide](03-Database/Security-Guide.md) | Credential storage, RBAC, audit logging, and encryption at rest |

---

## 4. API Reference

| Document | Description |
|---|---|
| [API Overview](04-API/API-Overview.md) | Request format, auth headers, and response envelope |
| [Authentication API](04-API/Authentication-API.md) | Login, logout, and session endpoints |
| [User API](04-API/User-API.md) | Customer-facing account and profile endpoints |
| [Admin API](04-API/Admin-API.md) | Admin management and reporting endpoints |
| [Staff API](04-API/Staff-API.md) | Staff portal endpoints |
| [Transaction API](04-API/Transaction-API.md) | Transfers, deposits, and withdrawals |
| [KYC API](04-API/KYC-API.md) | Document upload and verification endpoints |
| [Error Codes](04-API/Error-Codes.md) | Global HTTP status and error payload reference |
| [Rate Limiting](04-API/Rate-Limiting.md) | Request throttle thresholds and response headers |

---

## 5. Security

| Document | Description |
|---|---|
| [Security Overview](05-Security/Security-Overview.md) | High-level security architecture summary |
| [OWASP Compliance](05-Security/OWASP.md) | OWASP ASVS top-10 mitigations |
| [Threat Model](05-Security/Threat-Model.md) | Identified threat vectors and mitigations |
| [RBAC](05-Security/RBAC.md) | Role-based access control model |
| [Session Management](05-Security/Session-Management.md) | Cookie policy, timeout, and session fixation prevention |
| [Password Policy](05-Security/Password-Policy.md) | Hashing, complexity, and recovery rules |
| [Security Checklist](05-Security/Security-Checklist.md) | Pre-deployment security verification checklist |

---

## 6. Testing

| Document | Description |
|---|---|
| [Test Strategy](06-Testing/Test-Strategy.md) | Overarching philosophy and risk-management guidelines |
| [Test Plan](06-Testing/Test-Plan.md) | Tactical execution plan, deliverables, and release gating |
| [Unit Testing](06-Testing/Unit-Testing.md) | Isolated domain service testing guidelines |
| [Integration Testing](06-Testing/Integration-Testing.md) | Repository and database interaction testing |
| [Security Testing](06-Testing/Security-Testing.md) | Security boundary and vulnerability regression testing |
| [Performance Testing](06-Testing/Performance-Testing.md) | Load and stress testing guidelines |
| [UAT](06-Testing/UAT.md) | User acceptance testing process |

---

## 7. Deployment

| Document | Description |
|---|---|
| [Redis Upgrade Guide](07-Deployment/REDIS_UPGRADE.md) | Steps to migrate from file-cache to Redis for production scale |
