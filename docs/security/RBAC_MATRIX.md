# Role-Based Access Control (RBAC) Matrix

Finzo enforces a granular, multi-tier Role-Based Access Control model decoupling administration, branch operations, and customer self-service.

---

## 1. Actor Roles & Tiers

| Tier | Role Code | Scope & Responsibilities |
|---|---|---|
| **Executive / Admin** | `super_admin` | Global root authority with full access across all operations and security governance. |
| **Executive / Admin** | `admin` | General system administration, branch oversight, and standard user governance. |
| **Executive / Admin** | `moderator` | Compliance and identity review (KYC document adjudication, view-only audit logs). |
| **Branch Staff** | `branch_manager` | Local branch supervision, staff onboarding, shift approvals, and high-limit transactions. |
| **Branch Staff** | `teller` | Over-the-counter customer service, cash deposits, and withdrawal processing. |
| **Branch Staff** | `customer_support` | Customer ticket handling, profile lookups, and account status inquiries. |
| **Customer** | `customer` | Self-service domestic transfers, statement downloads, and security settings. |

---

## 2. Granular Permissions Inventory

Permissions are grouped into four core enterprise domains:

### Group 1: User & Customer Management
- `manage_users`: View, edit, suspend, and unlock retail customer accounts.
- `approve_application`: Review submitted KYC documents and approve/reject accounts.

### Group 2: Financial & Transaction Operations
- `process_transactions`: Execute deposits, withdrawals, and reverse erroneous operations.
- `manage_accounts`: Modify customer account tiers, limits, and statuses.

### Group 3: Staff & Branch Administration
- `manage_staff`: View staff rosters and operational metrics.
- `add_staff`: Create and onboard new branch staff accounts.
- `edit_staff`: Update contact details, branches, and designations.
- `suspend_staff`: Temporarily lock compromised or inactive staff accounts.
- `delete_staff`: Offboard staff personnel.
- `manage_branches`: Create and configure physical/logical bank branches.
- `add_branch`: Create new branch offices and servicing centers.

### Group 4: Audit & Governance
- `view_audit_logs`: Inspect forensic audit logs and actor activity.
- `manage_admins`: Create, modify, and unlock administrative accounts.
- `manage_roles`: Configure roles and dynamic permissions in the RBAC matrix.

---

## 3. Comprehensive Permissions Matrix

| Permission Key | Super Admin | Admin | Moderator | Branch Manager | Teller | Customer Support | Customer |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| `manage_users` | :white_check_mark: | :white_check_mark: | :x: | :white_check_mark: | :x: | :white_check_mark: (Read) | :x: |
| `approve_application` | :white_check_mark: | :white_check_mark: | :white_check_mark: | :white_check_mark: | :x: | :x: | :x: |
| `process_transactions` | :white_check_mark: | :white_check_mark: | :x: | :white_check_mark: | :white_check_mark: | :x: | :x: |
| `manage_accounts` | :white_check_mark: | :white_check_mark: | :x: | :white_check_mark: | :x: | :x: | :x: |
| `manage_staff` | :white_check_mark: | :white_check_mark: | :x: | :white_check_mark: | :x: | :x: | :x: |
| `add_staff` | :white_check_mark: | :white_check_mark: | :x: | :x: | :x: | :x: | :x: |
| `edit_staff` | :white_check_mark: | :white_check_mark: | :x: | :white_check_mark: | :x: | :x: | :x: |
| `suspend_staff` | :white_check_mark: | :white_check_mark: | :x: | :x: | :x: | :x: | :x: |
| `delete_staff` | :white_check_mark: | :x: | :x: | :x: | :x: | :x: | :x: |
| `manage_branches` | :white_check_mark: | :white_check_mark: | :x: | :x: | :x: | :x: | :x: |
| `add_branch` | :white_check_mark: | :white_check_mark: | :x: | :x: | :x: | :x: | :x: |
| `view_audit_logs` | :white_check_mark: | :white_check_mark: | :white_check_mark: | :white_check_mark: (Branch) | :x: | :x: | :x: |
| `manage_admins` | :white_check_mark: | :x: | :x: | :x: | :x: | :x: | :x: |
| `manage_roles` | :white_check_mark: | :x: | :x: | :x: | :x: | :x: | :x: |

---

## 4. Code Implementation Standards

RBAC checks are enforced using `SecurityService` and `RbacService` directly at controller entry points:

```php
// Administrator permission check
$this->security->checkAdminPermission($adminId, 'approve_application');

// Staff permission check
$this->security->checkStaffPermission($staffId, 'process_transactions');

// Strict Gate enforcement
$this->security->enforcePermission('manage_admins');
```
