# Agent Operational Manual

## 1. Project Overview
Finzo is an enterprise banking and financial platform. The technology stack consists of PHP 8.4 (Backend/API), MySQL 8.0, and vanilla JavaScript with Tailwind CSS (Frontend). The project architecture is heavily based on the Clean Architecture pattern, prioritizing isolated business logic, high security, enterprise-grade reliability, and strict adherence to modern PHP standards.

## 2. Agent Responsibilities
AI agents modifying this project must:
- Follow existing architecture strictly.
- Reuse existing code and components instead of reinventing them.
- Minimize unnecessary changes to keep pull requests focused.
- Maintain code quality and testing standards.
- Respect project conventions across all domains.

## 3. Core Development Rules
Agents must adhere to the following engineering principles:
- **Clean Architecture:** Keep infrastructure, routing, business logic, and presentation strictly decoupled.
- **SOLID:** Ensure single-responsibility and dependency inversion.
- **DRY (Don't Repeat Yourself):** Abstract common logic; do not duplicate code.
- **KISS (Keep It Simple, Stupid):** Keep implementations straightforward and maintainable.
- **PSR-12:** Follow PSR-12 coding standards strictly.
- **Consistent Code Style:** Maintain consistent variable naming, docblocks, and indentation throughout the codebase.

## 4. Project Structure Rules
When working inside the repository:
- Respect the existing folder structure (`app/`, `tests/`, `public/`, `docs/`).
- Do not create unnecessary files or folders.
- Follow existing naming conventions for classes, files, and variables.
- Keep modules logically organized by their bounded contexts.
- Do not duplicate functionality across different modules.

## 5. Implementation Rules
Strict implementation behaviors to enforce:
- **Thin Controllers:** Controllers must only handle routing, request validation, and JSON formatting.
- **Fat Services:** All business logic MUST reside in `Service` classes.
- **Repository Pattern:** Database interactions (`$this->db`) MUST exclusively reside in `Repository` classes.
- **Reuse:** Always reuse existing utilities (e.g., `ResponseBuilder`, `ExceptionHandler`, `Validator`) instead of creating ad-hoc solutions.
- **Precedence:** Follow existing patterns before attempting to introduce new architectural concepts.
- **Modularity:** Keep code highly modular, testable, and maintainable.

## 6. Modification Boundaries
The AI **must not** modify the following without explicit instructions from the user:
- Database schema and migration scripts
- Public APIs and routing endpoints
- Authentication flows
- Existing established features
- Project folder structure
- Configuration and CI/CD files

## 7. Security Rules
Agents must enforce these practical security rules:
- **Validate inputs:** Never trust user input. Use the central `Validator` service.
- **Escape outputs:** Always sanitize and escape data rendered in views to prevent XSS.
- **Parameterized queries:** Always use PDO prepared statements to prevent SQL injection.
- **Protect authentication:** Enforce strict session checks and CSRF validation on state-changing endpoints.
- **Respect authorization:** Enforce role-based access controls using `SecurityService`.
- **Never expose secrets:** Never log, print, or expose API keys, database credentials, or passwords.
- **Security architecture:** Follow the existing enterprise security middleware architecture.

## 8. Testing & Verification
The AI is required to verify every change before concluding:
- **Test modified functionality:** Ensure logic changes are covered by tests.
- **Ensure no syntax errors:** Run static analysis or linter checks.
- **Regression testing:** Ensure existing functionality still works by running the PHPUnit suite (`vendor/bin/phpunit`).
- **Verify responsive UI:** Ensure layout integrity when frontend HTML/CSS changes.
- **Verify database integrity:** Ensure transactions and foreign keys are respected when backend logic changes.

## 9. Documentation Policy
- Do **not** duplicate documentation.
- Update relevant architecture documentation inside the `docs/` folder whenever significant implementation changes require it.
- Keep `AGENTS.md` purely as an operational and behavioral guide, not a feature reference.

## 10. Completion Policy
The AI must never falsely report completion. Before marking work complete:
- Verify requested work is actually fully implemented.
- Verify no obvious errors, warnings, or broken tests remain.
- Clearly disclose any incomplete work, edge cases, or follow-up tasks required.
- Never claim "fully completed" without manual verification and testing.

## 11. Git Commit Policy
When executing large workflows that span multiple files, agents MUST NOT create a single monolithic commit at the end.
- **Commit Repeatedly & Atomically:** Group commits by logical change type (e.g., "Change the UI", "Fix JS", "Update Docs") and commit them sequentially as you progress.
- **One-Line Messages:** Format all commit messages in a single, concise line. Do not write multi-line commit descriptions.
