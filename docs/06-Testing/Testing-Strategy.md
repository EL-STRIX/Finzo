# Testing Strategy

## Metadata
- **Version:** 1.0.0
- **Status:** Approved
- **Owner:** Finzo QA Team
- **Last Updated:** July 2026

## Purpose
Define the QA framework and testing scope.

---

### Scope
- **Unit Testing:** Isolate and test individual \`Services\` (e.g., \`InterestCalculationService\`, \`KYCValidationService\`). Handled via **PHPUnit**.
- **Integration Testing:** Test \`Repositories\` against a dedicated testing database schema to verify PDO bindings.
- **API Testing:** Automated testing of \`routes/api.php\` using Postman/Newman collections or PHPUnit Feature tests.
- **Security Testing:** Automated SAST (Static Application Security Testing) in CI pipelines.

### Coverage Target
- **Core Financial Logic:** Minimum 90% Line Coverage required.
- **Controllers & Middleware:** Minimum 75% Coverage required.

---

## Revision History
| Version | Date | Author | Summary |
|---------|------|--------|---------|
| 1.0.0 | July 2026 | QA Lead | Established Testing Strategy. |
