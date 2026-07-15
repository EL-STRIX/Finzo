# Agent Behavioral Rules

These rules define the industry-standard development practices for the Finzo project. You must strictly adhere to these guidelines when writing or modifying code.

## 1. Architecture & Code Quality
- **Thin Controllers & Fat Services:** Controllers should only handle HTTP request/response routing and input validation. All business logic must reside in Service classes. Database interactions must be handled by Repository classes.
- **Centralized Routing:** Do NOT create standalone procedural PHP files (e.g., `login.php`) to act as endpoints. All traffic should be routed through a centralized Front Controller (`index.php`) using a standard routing pattern.
- **Dependency Injection:** Avoid `global` variables. Inject dependencies (like Database connections) via class constructors.
- **Strict Typing:** Use explicit type hints for all function parameters and return types in PHP.
- **Standards:** Follow PSR-12 coding standards for PHP. Keep methods short and focused on a single responsibility (SOLID principles). 
- **Anti-Pattern Warning:** Do NOT use Traits simply as a hack to split large files; break the class into smaller, logical sub-controllers instead.

## 2. Frontend & Design System
- **Strict Design System Compliance:** Always use the Finzo Enterprise Design System classes (`.ds-card`, `.ds-btn`, etc.). 
- **No Inline Styles:** Do not use `style="..."` attributes in HTML. Rely on Tailwind CSS utility classes if a specific token does not exist in the design system.

## 3. Security Best Practices
- **SQL Injection Prevention:** Never interpolate variables directly into SQL queries. Always use PDO prepared statements with parameterized queries.
- **Input Validation & Sanitization:** Never trust user input. Validate and sanitize all incoming request data before processing.
- **CSRF & XSS Protection:** Ensure all state-changing endpoints validate CSRF tokens. Always escape output when rendering data in HTML views.

## 4. Testing & Reliability
- **Testing:** Ensure that new business logic, service methods, and controller endpoints are accompanied by appropriate PHPUnit tests using the isolated `finzo_test_db`.
- **Error Handling:** Catch exceptions gracefully. Do not expose raw error messages or stack traces to the end user. Return generic, user-friendly error messages.

## 5. Version Control and Commits
- **Frequent, Atomic Commits:** Make frequent, atomic commits in the middle of your work, scaled to the number of logical changes.

## 6. Documentation
- **PHPDoc Blocks:** Add clear PHPDoc comments to classes and complex methods explaining their purpose, parameters, and return types.
- **Preserve Existing Comments:** Maintain the integrity of existing documentation. Do not remove unrelated comments or docstrings.
