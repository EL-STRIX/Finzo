# Risk Register & Roadmap

## Metadata
- **Version:** 1.0.0
- **Status:** Approved
- **Owner:** Finzo Product Management
- **Last Updated:** July 2026

## Purpose
Track active project risks and document the technical roadmap.

---

### Risk Register

| Risk ID | Category | Description | Mitigation Strategy | Owner | Status |
|---------|----------|-------------|---------------------|-------|--------|
| RSK-001 | Security | Direct Object Reference on KYC downloads | Enforce RBAC validation on all file stream endpoints. | SecOps | Active |
| RSK-002 | Performance | Slow query on Transactions table history | Implement cursor-based pagination and index optimization. | DBA | Active |
| RSK-003 | Tech Debt | Mixed procedural legacy code | Gradual refactor to PSR-4 Controller/Service architecture. | Arch | Active |

### Future Roadmap
- **Q3 2026:** Complete transition to modular API endpoints for all modules.
- **Q4 2026:** Implement Redis caching for high-traffic read operations (e.g., balance fetching).
- **Q1 2027:** Implement native Mobile App utilizing the standardized API.

---

## Revision History
| Version | Date | Author | Summary |
|---------|------|--------|---------|
| 1.0.0 | July 2026 | Product | Baseline Risk Register. |
