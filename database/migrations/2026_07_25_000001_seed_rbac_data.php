<?php

class SeedRbacData
{
    public function up(PDO $db): void
    {
        // 1. Roles
        $roles = [
            ['name' => 'super_admin', 'description' => 'Super Administrator with unrestricted access'],
            ['name' => 'admin', 'description' => 'Administrator with standard privileges'],
            ['name' => 'moderator', 'description' => 'Moderator with limited administrative access'],
            ['name' => 'manager', 'description' => 'Branch Manager'],
            ['name' => 'supervisor', 'description' => 'Branch Supervisor'],
            ['name' => 'teller', 'description' => 'Bank Teller'],
            ['name' => 'customer', 'description' => 'Standard Bank Customer']
        ];
        
        $roleIds = [];
        $stmt = $db->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
        foreach ($roles as $role) {
            $stmt->execute([$role['name'], $role['description']]);
            $roleIds[$role['name']] = $db->lastInsertId();
        }

        // 2. Permissions
        $permissions = [
            ['name' => 'manage_users', 'description' => 'Create, edit, or delete users'],
            ['name' => 'suspend_user', 'description' => 'Suspend a user account'],
            ['name' => 'reinstate_user', 'description' => 'Reinstate a suspended user'],
            ['name' => 'soft_delete_user', 'description' => 'Soft delete a user account'],
            ['name' => 'change_interest_rate', 'description' => 'Change system interest rates'],
            ['name' => 'add_branch', 'description' => 'Add a new bank branch'],
            ['name' => 'manage_admins', 'description' => 'Manage administrator accounts'],
            ['name' => 'suspend_staff', 'description' => 'Suspend a staff member'],
            ['name' => 'delete_staff', 'description' => 'Delete a staff member'],
            ['name' => 'add_staff', 'description' => 'Add a new staff member'],
            ['name' => 'approve_application', 'description' => 'Approve user applications'],
            ['name' => 'view_audit_logs', 'description' => 'View system audit logs'],
            ['name' => 'process_transactions', 'description' => 'Process customer transactions'],
            ['name' => 'manage_own_account', 'description' => 'Manage own customer account']
        ];
        
        $permIds = [];
        $stmt = $db->prepare("INSERT INTO permissions (name, description) VALUES (?, ?)");
        foreach ($permissions as $perm) {
            $stmt->execute([$perm['name'], $perm['description']]);
            $permIds[$perm['name']] = $db->lastInsertId();
        }

        // 3. Assign Permissions to Roles
        $rolePermissions = [
            'super_admin' => array_keys($permIds), // Super admin gets all permissions
            'admin' => ['add_staff', 'approve_application', 'view_audit_logs', 'process_transactions'], // Replicated from check_admin_permission logic
            'manager' => ['add_staff', 'suspend_staff', 'approve_application', 'process_transactions'],
            'supervisor' => ['approve_application', 'process_transactions'],
            'teller' => ['process_transactions'],
            'customer' => ['manage_own_account']
        ];

        $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($rolePermissions as $roleName => $perms) {
            if (!isset($roleIds[$roleName])) continue;
            
            $rId = $roleIds[$roleName];
            foreach ($perms as $permName) {
                if (!isset($permIds[$permName])) continue;
                $stmt->execute([$rId, $permIds[$permName]]);
            }
        }
        
        // 4. Sync existing admins to model_has_roles
        $stmt = $db->query("SELECT id, admin_role FROM admin_users");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $insertMhr = $db->prepare("INSERT IGNORE INTO model_has_roles (role_id, model_type, model_id) VALUES (?, ?, ?)");
        foreach ($admins as $admin) {
            $roleName = strtolower($admin['admin_role']);
            if (isset($roleIds[$roleName])) {
                $insertMhr->execute([$roleIds[$roleName], 'admin', $admin['id']]);
            }
        }
        
        // 5. Sync existing staff to model_has_roles
        $stmt = $db->query("SELECT id, designation FROM staff");
        $staffMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($staffMembers as $staff) {
            $roleName = strtolower($staff['designation']);
            if (isset($roleIds[$roleName])) {
                $insertMhr->execute([$roleIds[$roleName], 'staff', $staff['id']]);
            }
        }
        
        // 6. Sync existing customers
        $stmt = $db->query("SELECT id FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (isset($roleIds['customer'])) {
            foreach ($users as $user) {
                $insertMhr->execute([$roleIds['customer'], 'user', $user['id']]);
            }
        }
    }

    public function down(PDO $db): void
    {
        $db->exec("DELETE FROM role_permissions");
        $db->exec("DELETE FROM permissions");
        $db->exec("DELETE FROM roles");
    }
}
