<?php

namespace App\Infrastructure\Repositories;

use PDO;
use App\Shared\Core\Database;
use App\Domain\Contracts\Repositories\RoleRepositoryInterface;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{

    public function getAllRoles(): array
    {
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY id ASC");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all assigned permissions in one batch query
        $permStmt = $this->db->query("
            SELECT rp.role_id, p.id, p.name, p.description, p.group_name
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            ORDER BY p.id ASC
        ");
        $allPerms = $permStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $permsByRole = [];
        foreach ($allPerms as $p) {
            $permsByRole[$p['role_id']][] = [
                'id' => (int)$p['id'],
                'name' => $p['name'],
                'description' => $p['description'],
                'group_name' => $p['group_name'] ?? ''
            ];
        }

        foreach ($roles as &$role) {
            $role['id'] = (int)$role['id'];
            $role['permissions'] = $permsByRole[$role['id']] ?? [];
            $role['permissions_count'] = count($role['permissions']);
            $role['is_system'] = in_array($role['name'], ['super_admin', 'admin', 'moderator', 'manager', 'supervisor', 'teller', 'customer'], true);
        }
        unset($role);

        return $roles;
    }

    public function getRoleById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$role) {
            return null;
        }
        $role['id'] = (int)$role['id'];
        $role['permissions'] = $this->getRolePermissions($id);
        $role['permissions_count'] = count($role['permissions']);
        $role['is_system'] = in_array($role['name'], ['super_admin', 'admin', 'moderator', 'manager', 'supervisor', 'teller', 'customer'], true);
        return $role;
    }

    public function getRoleByName(string $name): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE name = ?");
        $stmt->execute([$name]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        return $role ?: null;
    }

    public function createRole(string $name, ?string $description = null): int
    {
        $stmt = $this->db->prepare("INSERT INTO roles (name, description, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->execute([$name, $description]);
        return (int)$this->db->lastInsertId();
    }

    public function updateRole(int $id, string $name, ?string $description = null): bool
    {
        $stmt = $this->db->prepare("UPDATE roles SET name = ?, description = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$name, $description, $id]);
    }

    public function deleteRole(int $id): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt1 = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt1->execute([$id]);

            $stmt2 = $this->db->prepare("DELETE FROM model_has_roles WHERE role_id = ?");
            $stmt2->execute([$id]);

            $stmt3 = $this->db->prepare("DELETE FROM roles WHERE id = ?");
            $res = $stmt3->execute([$id]);

            $this->db->commit();
            return $res;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getAllPermissions(): array
    {
        $stmt = $this->db->query("SELECT * FROM permissions ORDER BY group_name ASC, name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRolePermissions(int $roleId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.* FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignPermissionsToRole(int $roleId, array $permissionIds): void
    {
        $this->db->beginTransaction();
        try {
            $stmtDelete = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmtDelete->execute([$roleId]);

            if (!empty($permissionIds)) {
                $placeholders = implode(',', array_fill(0, count($permissionIds), '(?, ?)'));
                $values = [];
                foreach ($permissionIds as $pid) {
                    $values[] = $roleId;
                    $values[] = $pid;
                }
                
                $stmtInsert = $this->db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES $placeholders");
                $stmtInsert->execute($values);
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getModelRoles(string $modelType, int $modelId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.* FROM roles r
            JOIN model_has_roles mhr ON r.id = mhr.role_id
            WHERE mhr.model_type = ? AND mhr.model_id = ?
        ");
        $stmt->execute([$modelType, $modelId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function syncModelRoles(string $modelType, int $modelId, array $roleIds): void
    {
        $this->db->beginTransaction();
        try {
            $stmtDelete = $this->db->prepare("DELETE FROM model_has_roles WHERE model_type = ? AND model_id = ?");
            $stmtDelete->execute([$modelType, $modelId]);

            if (!empty($roleIds)) {
                $placeholders = implode(',', array_fill(0, count($roleIds), '(?, ?, ?)'));
                $values = [];
                foreach ($roleIds as $rid) {
                    $values[] = $rid;
                    $values[] = $modelType;
                    $values[] = $modelId;
                }
                
                $stmtInsert = $this->db->prepare("INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES $placeholders");
                $stmtInsert->execute($values);
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
