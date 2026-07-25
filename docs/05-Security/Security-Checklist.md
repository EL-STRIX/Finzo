---
title: "Developer Security Checklist"
category: "Security"
module: "Governance"
last_updated: "2026-07-25"
---

# Developer Security Checklist

## 1. Objective
Before any pull request is merged into the `main` branch, developers MUST verify their code against this mandatory security checklist. This ensures the baseline established in Phase 17 is continuously upheld.

## 2. Authentication & Authorization
- [ ] **No Hardcoded Roles:** Are you using `$this->security->enforcePermission('permission_name')` instead of hardcoding roles in your controllers?
- [ ] **Route Protection:** Is your new route wrapped in the appropriate middleware (`Auth::requireAdmin()`, `Auth::requireCustomer()`)?
- [ ] **Object-Level Authorization (IDOR):** If your endpoint fetches or mutates data, did you verify that the `user_id` of the resource matches `$_SESSION['user_id']`?
- [ ] **MFA Readiness:** If you are building a critical action (e.g., Wire Transfer), does it support step-up authentication checks?

## 3. Data Integrity & Validation
- [ ] **Prepared Statements:** Are all database queries utilizing PDO Prepared Statements? (No string concatenation for SQL).
- [ ] **Input Validation:** Is `$_POST` and `$_GET` data routed through `App\Shared\Core\Validation\Validator`?
- [ ] **Output Escaping:** Are you using `htmlspecialchars()` in your frontend views when echoing user-supplied data?

## 4. Session & State
- [ ] **CSRF Protection:** If your endpoint changes state (POST, PUT, DELETE), does it expect the `X-CSRF-TOKEN` header? (Handled automatically by `CsrfMiddleware`, but ensure your frontend sends it).
- [ ] **No Direct Session Mutation:** Are you relying on `SessionManager` instead of manually setting `$_SESSION` arrays inside Domain Services?

## 5. Error Handling
- [ ] **No Stack Traces:** Are you returning generic API JSON error messages instead of leaking PDO exceptions to the client?
