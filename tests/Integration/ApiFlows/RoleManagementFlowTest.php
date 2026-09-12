<?php

namespace Tests\Integration\ApiFlows;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use Tests\Factories\AdminFactory;

/**
 * End-to-end workflow tests for the Admin Roles & Permissions feature.
 *
 * Validates the complete stack for every endpoint:
 * Router -> AdminAuthMiddleware -> RoleController -> RoleService -> RoleRepository -> MySQL
 *
 * Note: The RoleRepository manages its own transactions (BEGIN/COMMIT), so this
 * suite releases the TestCase isolation transaction and cleans up explicitly.
 */
class RoleManagementFlowTest extends TestCase
{
    /** @var int[] Role ids created during the test (cleaned up in tearDown) */
    private array $createdRoleIds = [];

    /** @var int[] Admin ids created during the test (cleaned up in tearDown) */
    private array $createdAdminIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Release the outer isolation transaction so repository-level
        // transactions (create/assign/delete) can operate normally.
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->createdRoleIds as $roleId) {
            $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$roleId]);
            $this->db->prepare("DELETE FROM model_has_roles WHERE role_id = ?")->execute([$roleId]);
            $this->db->prepare("DELETE FROM roles WHERE id = ?")->execute([$roleId]);
        }
        foreach ($this->createdAdminIds as $adminId) {
            $this->db->prepare("DELETE FROM model_has_roles WHERE model_type = 'admin' AND model_id = ?")->execute([$adminId]);
            $this->db->prepare("DELETE FROM admin_users WHERE id = ?")->execute([$adminId]);
        }
        parent::tearDown();
    }

    private function createAdminSession(string $adminRole = 'super_admin'): array
    {
        $adminId = AdminFactory::createAdmin(['admin_role' => $adminRole]);
        $this->createdAdminIds[] = $adminId;
        return ['role' => 'admin', 'admin_id' => $adminId, 'admin_role' => $adminRole];
    }

    private function permissionId(string $name): int
    {
        $stmt = $this->db->prepare("SELECT id FROM permissions WHERE name = ?");
        $stmt->execute([$name]);
        $id = $stmt->fetchColumn();
        $this->assertNotFalse($id, "Seeded permission '{$name}' must exist");
        return (int)$id;
    }

    private function roleIdByName(string $name): int
    {
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute([$name]);
        $id = $stmt->fetchColumn();
        $this->assertNotFalse($id, "Role '{$name}' must exist");
        return (int)$id;
    }

    private function unwrap(callable $fn): array
    {
        try {
            $res = $fn();
            if (is_array($res)) return $res;
            if (is_string($res)) {
                $decoded = json_decode($res, true);
                if (is_array($decoded)) return $decoded;
            }
            return [];
        } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
            $decoded = json_decode($e->getMessage(), true);
            if (is_array($decoded) && isset($decoded['status']) && (int)$decoded['status'] >= 400) {
                throw $e;
            }
            if (is_array($decoded) && isset($decoded['success']) && $decoded['success'] === false) {
                throw $e;
            }
            return $decoded ?: [];
        }
    }

    /** GET /api/v1/admin/roles without a session must be rejected with 401 */
    public function testUnauthenticatedRoleListingIsRejected(): void
    {
        try {
            RequestHelper::getJson('api/v1/admin/roles', [], []);
            $this->fail('Expected authentication failure');
        } catch (\Throwable $e) {
            $body = json_decode($e->getMessage(), true);
            $this->assertEquals(401, $body['status'] ?? null, $e->getMessage());
        }
    }

    /** An admin without manage_admins/manage_roles (and not super_admin) is denied: deny-by-default */
    public function testNonPrivilegedAdminCannotAccessRoleEndpoints(): void
    {
        $session = $this->createAdminSession('moderator');

        try {
            RequestHelper::getJson('api/v1/admin/roles', [], $session);
            $this->fail('Expected authorization failure');
        } catch (\Throwable $e) {
            $body = json_decode($e->getMessage(), true);
            $this->assertEquals(403, $body['status'] ?? null, $e->getMessage());
        }
    }
    /** Validates the RoleController::checkAccess super-admin fast-path (session admin_role) */
    public function testSuperAdminSessionCanListRolesWithoutRoleMappings(): void
    {
        $session = $this->createAdminSession('super_admin');

        // Guarantee no model_has_roles mapping exists for this admin
        $this->db->prepare("DELETE FROM model_has_roles WHERE model_type = 'admin' AND model_id = ?")
            ->execute([$session['admin_id']]);

        $response = $this->unwrap(fn() => RequestHelper::getJson('api/v1/admin/roles', [], $session));

        $this->assertTrue($response['success'], 'Super admin fast-path must grant access');
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data'], 'Seeded roles must be returned');

        $first = $response['data'][0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('permissions', $first);
        $this->assertArrayHasKey('permissions_count', $first);
        $this->assertArrayHasKey('is_system', $first);
        $this->assertIsInt($first['id']);
        $this->assertIsArray($first['permissions']);
    }

    /** Full lifecycle: create (with permissions) -> read -> update (permission sync) -> delete */
    public function testFullRoleLifecycleWorkflow(): void
    {
        $session = $this->createAdminSession();
        $permUsers = $this->permissionId('manage_users');
        $permAudit = $this->permissionId('view_audit_logs');
        $unique = 'e2e_role_' . substr(md5(uniqid('', true)), 0, 10);

        // 1. CREATE with two permissions
        $createRes = $this->unwrap(fn() => RequestHelper::postJson('api/v1/admin/roles', [
            'name' => $unique,
            'description' => 'E2E lifecycle role',
            'permissions' => [$permUsers, $permAudit],
        ], $session));

        $this->assertTrue($createRes['success'] ?? false, (string)json_encode($createRes));
        $roleId = (int)$createRes['data']['id'];
        $this->createdRoleIds[] = $roleId;

        // DB verification: role row + exactly two permission mappings
        $stmt = $this->db->prepare("SELECT name, description FROM roles WHERE id = ?");
        $stmt->execute([$roleId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals($unique, $row['name']);
        $this->assertEquals('E2E lifecycle role', $row['description']);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$roleId]);
        $this->assertEquals(2, (int)$stmt->fetchColumn(), 'Both permissions must be mapped');

        // 2. READ single role
        $showRes = $this->unwrap(fn() => RequestHelper::getJson("api/v1/admin/roles/{$roleId}", [], $session));
        $this->assertTrue($showRes['success'] ?? false);
        $this->assertCount(2, $showRes['data']['permissions']);

        // 3. UPDATE name/description and sync down to one permission
        $renamed = $unique . '_v2';
        $updateRes = $this->unwrap(fn() => RequestHelper::putJson("api/v1/admin/roles/{$roleId}", [
            'name' => $renamed,
            'description' => 'Updated description',
            'permissions' => [$permAudit],
        ], $session));

        $this->assertTrue($updateRes['success'] ?? false, (string)json_encode($updateRes));
        $this->assertCount(1, $updateRes['data']['permissions']);
        $this->assertEquals($permAudit, (int)$updateRes['data']['permissions'][0]['id']);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$roleId]);
        $this->assertEquals(1, (int)$stmt->fetchColumn(), 'Permission sync must replace mappings');

        // 4. DELETE
        $deleteRes = $this->unwrap(fn() => RequestHelper::deleteJson("api/v1/admin/roles/{$roleId}", [], $session));
        $this->assertTrue($deleteRes['success'] ?? false, (string)json_encode($deleteRes));

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM roles WHERE id = ?");
        $stmt->execute([$roleId]);
        $this->assertEquals(0, (int)$stmt->fetchColumn(), 'Role must be removed');

        // Already deleted: stop tracking it
        $this->createdRoleIds = array_values(array_diff($this->createdRoleIds, [$roleId]));
    }

    /** Duplicate names and names exceeding the VARCHAR(50) column must return 422 */
    public function testCreateRoleRejectsDuplicateAndOversizedName(): void
    {
        $session = $this->createAdminSession();
        $unique = 'e2e_dup_' . substr(md5(uniqid('', true)), 0, 10);

        $createRes = $this->unwrap(fn() => RequestHelper::postJson('api/v1/admin/roles', ['name' => $unique], $session));
        $this->assertTrue($createRes['success'] ?? false);
        $this->createdRoleIds[] = (int)$createRes['data']['id'];

        // Duplicate name
        try {
            RequestHelper::postJson('api/v1/admin/roles', ['name' => $unique], $session);
            $this->fail('Expected duplicate-name rejection');
        } catch (\Throwable $e) {
            $body = json_decode($e->getMessage(), true);
            $this->assertEquals(422, $body['status'] ?? null, $e->getMessage());
        }

        // Over-length name (DB column is VARCHAR(50))
        try {
            RequestHelper::postJson('api/v1/admin/roles', ['name' => str_repeat('x', 60)], $session);
            $this->fail('Expected over-length name rejection');
        } catch (\Throwable $e) {
            $body = json_decode($e->getMessage(), true);
            $this->assertEquals(422, $body['status'] ?? null, $e->getMessage());
        }
    }

    /** System roles must be protected from deletion */
    public function testProtectedSystemRoleCannotBeDeleted(): void
    {
        $session = $this->createAdminSession();
        $adminRoleId = $this->roleIdByName('admin');

        try {
            RequestHelper::deleteJson("api/v1/admin/roles/{$adminRoleId}", [], $session);
            $this->fail('Expected protected-role rejection');
        } catch (\Throwable $e) {
            $body = json_decode($e->getMessage(), true);
            $this->assertEquals(403, $body['status'] ?? null, $e->getMessage());
        }
    }

    /** A role assigned to an admin via model_has_roles cannot be deleted (400) */
    public function testDeleteIsBlockedWhileRoleIsAssigned(): void
    {
        $session = $this->createAdminSession();
        $unique = 'e2e_inuse_' . substr(md5(uniqid('', true)), 0, 10);

        $createRes = $this->unwrap(fn() => RequestHelper::postJson('api/v1/admin/roles', ['name' => $unique], $session));
        $roleId = (int)$createRes['data']['id'];
        $this->createdRoleIds[] = $roleId;

        // Assign the role to the acting admin via the assign-roles endpoint
        $assignRes = $this->unwrap(fn() => RequestHelper::postJson('api/v1/admin/assign-roles', [
            'model_type' => 'admin',
            'model_id' => $session['admin_id'],
            'role_ids' => [$roleId],
        ], $session));
        $this->assertTrue($assignRes['success'] ?? false, (string)json_encode($assignRes));

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM model_has_roles WHERE role_id = ? AND model_type = 'admin' AND model_id = ?");
        $stmt->execute([$roleId, $session['admin_id']]);
        $this->assertEquals(1, (int)$stmt->fetchColumn(), 'Assignment must be persisted');

        // Deletion must now be blocked because the role is in use
        try {
            RequestHelper::deleteJson("api/v1/admin/roles/{$roleId}", [], $session);
            $this->fail('Expected in-use role deletion to be blocked');
        } catch (\Throwable $e) {
            $body = json_decode($e->getMessage(), true);
            $this->assertEquals(400, $body['status'] ?? null, $e->getMessage());
        }
    }
}
