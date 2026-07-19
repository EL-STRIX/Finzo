# Contributing Guidelines

## Metadata
- **Version:** 1.0.0
- **Status:** Approved
- **Owner:** Finzo Engineering
- **Last Updated:** July 2026

## Purpose
Define the standard workflow for committing code and opening Pull Requests in the Finzo repository.

---

### Git Workflow
We use a standard branching strategy:
- \`main\` - Production-ready, stable code.
- \`develop\` - Integration branch for active development.
- \`feature/ticket-name\` - Branch for individual features.
- \`hotfix/issue-name\` - Branch for emergency production patches.

### Pull Request Guide
All Pull Requests must:
1. Target the \`develop\` branch (unless it's a hotfix).
2. Pass all automated CI tests (PHPUnit and PHP_CodeSniffer).
3. Include at least 1 approval from a Peer Reviewer.
4. Document any architectural changes in the \`docs/ADR/\` directory.

### Code Standards
- **PHP:** PSR-12 standard enforced via Code Sniffer.
- **JavaScript:** ES6 modules, strict mode required.
- **Documentation:** Any changes to UI/UX must update the corresponding markdown in \`docs/00-FEPS/\`.

---

## Revision History
| Version | Date | Author | Summary |
|---------|------|--------|---------|
| 1.0.0 | July 2026 | Architect | Initial Guidelines. |
