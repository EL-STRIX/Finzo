<?php

namespace App\Infrastructure\Repositories;

use PDO;
use App\Shared\Core\Database;
use App\Domain\Contracts\Repositories\RoleRepositoryInterface;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{

    public function getAllRoles(): array
    {
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoleById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        return $role ?: null;
    }

    public function getRoleByName(string $name): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE name = ?");
        $stmt->execute([$name]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        return $role ?: null;
    }

    public function createRole(string $name): int
    {
        $stmt = $this->db->prepare("INSERT INTO roles (name) VALUES (?)");
        $stmt->execute([$name]);
        return (int)$this->db->lastInsertId();
    }

    public function updateRole(int $id, string $name): bool
    {
        $stmt = $this->db->prepare("UPDATE roles SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }

    public function deleteRole(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM roles WHERE id = ?");
        return $stmt->execute([$id]);
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
