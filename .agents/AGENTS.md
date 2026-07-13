# Agent Behavioral Rules

These rules define the industry-standard development practices for the Finzo project. You must strictly adhere to these guidelines when writing or modifying code.

## 1. Version Control and Commits
- **Frequent, Atomic Commits:** Do not wait until the end of a large feature or refactor to commit. Make frequent, atomic commits in the middle of your work.
- **Commit Per Logical Change:** Commit changes immediately after completing a specific page, module, or bug fix before moving on to the next one. The number of commits should naturally scale with the number of distinct file changes made during a task.

## 2. Architecture & Code Quality
- **Thin Controllers & Fat Services:** Controllers should only handle HTTP request/response routing and input validation. All business logic must reside in Service classes. Database interactions must be handled by Repository classes.
- **Strict Typing:** Use explicit type hints for all function parameters and return types in PHP.
- **Standards:** Follow PSR-12 coding standards for PHP. Keep methods short and focused on a single responsibility (SOLID principles).

## 3. Security Best Practices
- **SQL Injection Prevention:** Never interpolate variables directly into SQL queries. Always use PDO prepared statements with parameterized queries.
- **Input Validation & Sanitization:** Never trust user input. Validate and sanitize all `$_GET`, `$_POST`, and other incoming request data before processing.
- **CSRF & XSS Protection:** Ensure all state-changing endpoints validate CSRF tokens. Always escape output when rendering data in HTML views to prevent Cross-Site Scripting (XSS).

## 4. Testing & Reliability
- **Testing:** Ensure that new business logic, service methods, and controller endpoints are accompanied by appropriate PHPUnit tests. Tests must pass before work is considered complete.
- **Error Handling:** Catch exceptions gracefully. Do not expose raw error messages or stack traces to the end user. Return generic, user-friendly error messages (e.g., standard JSON error responses for APIs).

## 5. Documentation
- **PHPDoc Blocks:** Add clear PHPDoc comments to classes and complex methods explaining their purpose, parameters, and return types.
- **Preserve Existing Comments:** Maintain the integrity of existing documentation. Do not remove unrelated comments or docstrings.
