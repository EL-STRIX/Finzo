---
title: "Role-Based Access Control (RBAC)"
category: "Security"
module: "Authorization"
last_updated: "2026-07-25"
---

# Role-Based Access Control (RBAC)

## 1. Overview
In Phase 17.2, the Finzo Banking System completely migrated from hardcoded legacy role arrays to a dynamic, database-backed Role-Based Access Control (RBAC) schema. This permits infinite scalability, dynamic capability assignment, and strict granular compliance.

## 2. Database Schema Architecture

The RBAC implementation relies on three heavily indexed tables with strict foreign key constraints:
- **`roles`**: Contains distinct business personas (`super_admin`, `admin`, `manager`, `teller`, `auditor`).
- **`permissions`**: Contains granular system capabilities (`manage_admins`, `approve_loans`, `view_reports`).
- **`role_permissions`**: A pivot table establishing a many-to-many relationship mapping roles to their capabilities.

*Constraints*: Deleting a role cascade-deletes all pivot entries.

## 3. RbacService implementation

`App\Shared\Security\RbacService` is responsible for querying the database to resolve permission clusters.

### Resolution Flow:
1. `SecurityService` calls `$rbac->getPermissionsForRole($role_name)`.
2. The query performs a standard JOIN operation between `permissions` and `role_permissions`.
3. If the user possesses the required capability, `hasPermission()` returns `true`.

## 4. Internal Caching (Performance)
Because checking capabilities requires database lookups, the `RbacService` implements a per-request static cache.
```php
private array $rolePermissionsCache = [];
```
Once a role's permissions are fetched, they are stored in this array. Subsequent checks for the same role during the exact same PHP request lifecycle cost 0 database queries, resulting in <1ms latency for high-throughput endpoints.
