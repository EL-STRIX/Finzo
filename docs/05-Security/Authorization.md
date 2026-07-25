---
title: "Authorization Architecture"
category: "Security"
module: "Authorization"
last_updated: "2026-07-25"
---

# Authorization Architecture

## 1. Overview
Authorization in the Finzo Banking System determines what an authenticated user is permitted to do. The system enforces a strict **Deny by Default** paradigm. All sensitive routes and controller methods are heavily fortified using middleware and the centralized `SecurityService`.

## 2. Core Components

### SecurityService
The `App\Shared\Security\SecurityService` class is the master arbiter of access control. It encapsulates the `RbacService` and provides global helper methods to enforce constraints dynamically.
- `hasPermission(string $permission)`: Evaluates if the currently authenticated user's role has the requested capability.
- `enforcePermission(string $permission)`: Throws a fatal `AuthorizationException` if the user lacks the permission.

### Route-Level Protection (Middleware)
Controllers are protected by standard route definitions using the `Auth` static helper in `Router.php`.
```php
// Only system administrators can access this endpoint
$router->post('/api/v1/admin/manage', 'AdminController@manage', [Auth::class, 'requireAdmin']);

// Only logged-in customers can access this endpoint
$router->get('/api/v1/account/balance', 'AccountController@balance', [Auth::class, 'requireCustomer']);
```

### Resource Ownership Validation
For Customer-facing APIs, the `UserService` and `AccountService` must always assert that the `user_id` being manipulated matches the `$_SESSION['user_id']`. Cross-account manipulation (IDOR) is prevented strictly at the domain service layer.

## 3. Deprecated Patterns
- **Gate Class:** The legacy `Gate::authorize()` array-based hardcoded mapping system was completely deleted in Phase 17. Controllers MUST use `$this->security->enforcePermission('permission_name')` injected via dependency injection.
