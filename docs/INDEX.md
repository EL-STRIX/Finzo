# Finzo Enterprise Platform Documentation Index

> Enterprise Banking System — Technical Documentation for Developers, DBAs, Security Engineers, and Auditors.

---

## 1. Master Reports & Engineering Studies

| Document | Description |
|---|---|
| [Project Engineering Report](reports/PROJECT_REPORT.md) | Comprehensive architecture, auth flows, and engineering specifications |
| [Engineering Case Study](case-study/FINZO_CASE_STUDY.md) | In-depth architectural case study and design rationale |
| [Security Audit](reports/Security-Audit.md) | Security architecture, threat modeling, and defensive boundaries |
| [Accessibility Audit](reports/Accessibility-Audit.md) | Formal WCAG 2.2 AA audit and verification |

---

## 2. Architecture & Design Standards

| Document | Description |
|---|---|
| [Architecture Overview](ARCHITECTURE.md) | Clean architecture, design patterns, and request lifecycle |
| [Design System](design-system/FINZO_DESIGN_SYSTEM.md) | CSS tokens, component library, typography, and accessibility rules |
| [Glossary of Terms](GLOSSARY.md) | Banking, financial, and domain terminology reference |
| [Changelog](CHANGELOG.md) | Platform release milestones and version history |

---

## 3. Database Architecture

| Document | Description |
|---|---|
| [Database Architecture](database/README.md) | Database design philosophy, schema layers, and financial integrity |
| [Schema Reference](database/SCHEMA_REFERENCE.md) | Complete table dictionary, columns, constraints, and relationships |
| [Migration Guide](database/MIGRATION_GUIDE.md) | DBA runbook — migrations, seeders, backup, and disaster recovery |

---

## 4. API Reference

| Document | Description |
|---|---|
| [API Overview](api/README.md) | Request format, auth headers, response envelope, error codes & rate limits |
| [Authentication API](api/AUTH_API.md) | Login, logout, and session verification endpoints |
| [Customer User API](api/USER_API.md) | Customer profile, KYC submission with PIN setup, and account queries |
| [Branch Staff API](api/STAFF_API.md) | Staff teller workstation, shift tracking, deposits, withdrawals & receipts |
| [Administrative API](api/ADMIN_API.md) | Admin operations, KYC approvals, RBAC matrix, and audit logging |
| [Transaction API](api/TRANSACTION_API.md) | Fund transfers with 4-digit PIN verification, beneficiaries & reversals |

---

## 5. Security & Compliance

| Document | Description |
|---|---|
| [Security Architecture](security/README.md) | Zero-Trust architecture, decoupled auth, session policy & PII encryption |
| [RBAC Permissions Matrix](security/RBAC_MATRIX.md) | Granular Role-Based Access Control matrix aligned with code implementation |
| [OWASP ASVS Compliance](security/OWASP_COMPLIANCE.md) | OWASP ASVS Top-10 mitigations, threat model, and pre-deployment checklist |

---

## 6. Testing & Quality Assurance

| Document | Description |
|---|---|
| [Testing Strategy Guide](testing/README.md) | Test architecture, PHPUnit execution, database isolation, and CI gating |
| [Core Banking Test Cases](testing/TEST_CASES.md) | Automated and manual banking verification scenarios |

---

## 7. Production Deployment & Operations

| Document | Description |
|---|---|
| [Production Deployment Runbook](deployment/README.md) | Server requirements, Nginx & Apache configurations, PHP 8.4 hardening |
| [Redis Scaling Guide](deployment/REDIS_UPGRADE.md) | Steps to scale cache and background job queues via Redis |
