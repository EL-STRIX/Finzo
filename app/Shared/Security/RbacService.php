<?php

namespace App\Shared\Security;

use PDO;
use App\Shared\Core\Database;

class RbacService
{
    private PDO $db;
    
    private static ?RbacService $instance = null;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Assigns a role to a user/admin/staff model
     */
    public function assignRole(string $modelType, int $modelId, string $roleName): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute([$roleName]);
        $roleId = $stmt->fetchColumn();

        if (!$roleId) return false;

        $stmt = $this->db->prepare("
            INSERT IGNORE INTO model_has_roles (role_id, model_type, model_id)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$roleId, $modelType, $modelId]);
    }

    /**
     * Checks if a user has a specific permission. Denies by default (PoLP).
     */
    public function can(string $modelType, int $modelId, string $permissionName): bool
    {
        // 1. Direct super_admin check on model table (admin_users table admin_role = 'super_admin')
        if ($modelType === 'admin') {
            $stmt = $this->db->prepare("SELECT admin_role FROM admin_users WHERE id = ?");
            $stmt->execute([$modelId]);
            $adminRole = $stmt->fetchColumn();
            if ($adminRole === 'super_admin') {
                return true;
            }
        }

        // 2. Direct permission through role mapping
        $stmt = $this->db->prepare("
            SELECT 1 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN model_has_roles mhr ON rp.role_id = mhr.role_id
            WHERE mhr.model_type = ? AND mhr.model_id = ? AND p.name = ?
            LIMIT 1
        ");
        $stmt->execute([$modelType, $modelId, $permissionName]);
        $hasDirect = (bool)$stmt->fetchColumn();
        
        if ($hasDirect) {
            return true;
        }
        
        // 3. Super admin bypass check in model_has_roles
        $stmt = $this->db->prepare("
            SELECT 1 
            FROM roles r
            JOIN model_has_roles mhr ON r.id = mhr.role_id
            WHERE mhr.model_type = ? AND mhr.model_id = ? AND r.name = 'super_admin'
            LIMIT 1
        ");
        $stmt->execute([$modelType, $modelId]);
        if ((bool)$stmt->fetchColumn()) {
            return true;
        }

        // 4. Standard admin role fallback on admin_users table
        if ($modelType === 'admin' && isset($adminRole) && $adminRole === 'admin') {
            $stmt = $this->db->prepare("
                SELECT 1 
                FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                JOIN roles r ON rp.role_id = r.id
                WHERE r.name = 'admin' AND p.name = ?
                LIMIT 1
            ");
            $stmt->execute([$permissionName]);
            if ((bool)$stmt->fetchColumn()) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Checks if a user has a specific role.
     */
    public function hasRole(string $modelType, int $modelId, string $roleName): bool
    {
        if ($modelType === 'admin') {
            $stmt = $this->db->prepare("SELECT admin_role FROM admin_users WHERE id = ?");
            $stmt->execute([$modelId]);
            $adminRole = $stmt->fetchColumn();
            if ($adminRole === $roleName) {
                return true;
            }
        }

        $stmt = $this->db->prepare("
            SELECT 1 
            FROM roles r
            JOIN model_has_roles mhr ON r.id = mhr.role_id
            WHERE mhr.model_type = ? AND mhr.model_id = ? AND r.name = ?
            LIMIT 1
        ");
        $stmt->execute([$modelType, $modelId, $roleName]);
        return (bool)$stmt->fetchColumn();
    }
}
