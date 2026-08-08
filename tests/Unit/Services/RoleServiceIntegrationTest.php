<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\RoleService;
use App\Infrastructure\Repositories\RoleRepository;

/**
 * Integration tests for RoleService and RoleRepository.
 * Covers all major code paths using the real test database.
 */
class RoleServiceIntegrationTest extends TestCase
{
    private RoleService $roleService;
    private RoleRepository $roleRepo;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('FINZO_ROOT')) {
            define('FINZO_ROOT', dirname(dirname(dirname(__DIR__))));
        }
        require_once FINZO_ROOT . '/config/functions.php';

        if (!defined('APP_KEY')) {
            define('APP_KEY', base64_encode(str_repeat('a', 32)));
        }

        $this->roleRepo = new RoleRepository();
        $this->roleService = new RoleService($this->roleRepo);
    }

    public function testGetRolesReturnsArray(): void
    {
        $result = $this->roleService->getRoles();
        $this->assertIsArray($result);
    }

    public function testGetPermissionsReturnsArray(): void
    {
        $result = $this->roleService->getPermissions();
        $this->assertIsArray($result);
    }

    public function testGetRoleDetailsThrowsForMissingRole(): void
    {
        $this->expectException(\App\Shared\Core\Exceptions\ApiException::class);
        $this->roleService->getRoleDetails(999999);
    }

    public function testCreateRoleThrowsWhenNameEmpty(): void
    {
        $this->expectException(\App\Domain\Exceptions\ValidationException::class);
        $this->roleService->createRole([]);
    }

    public function testUpdateRoleThrowsForMissingRole(): void
    {
        $this->expectException(\App\Shared\Core\Exceptions\ApiException::class);
        $this->roleService->updateRole(999999, ['name' => 'test']);
    }

    public function testDeleteRoleThrowsForMissingRole(): void
    {
        $this->expectException(\App\Shared\Core\Exceptions\ApiException::class);
        $this->roleService->deleteRole(999999);
    }

    public function testGetModelRolesThrowsForInvalidType(): void
    {
        $this->expectException(\App\Shared\Core\Exceptions\ApiException::class);
        $this->roleService->getModelRoles('invalid_type', 1);
    }

    public function testGetModelRolesForValidType(): void
    {
        $result = $this->roleService->getModelRoles('admin', 999999);
        $this->assertIsArray($result);
    }

    public function testSyncModelRolesThrowsForInvalidType(): void
    {
        $this->expectException(\App\Shared\Core\Exceptions\ApiException::class);
        $this->roleService->syncModelRoles('invalid_type', 1, []);
    }

    public function testRepositoryGetAllRoles(): void
    {
        $result = $this->roleRepo->getAllRoles();
        $this->assertIsArray($result);
    }

    public function testRepositoryGetAllPermissions(): void
    {
        $result = $this->roleRepo->getAllPermissions();
        $this->assertIsArray($result);
    }

    public function testRepositoryGetRoleByIdReturnsNull(): void
    {
        $result = $this->roleRepo->getRoleById(999999);
        $this->assertNull($result);
    }

    public function testRepositoryGetRoleByNameReturnsNull(): void
    {
        $result = $this->roleRepo->getRoleByName('nonexistent_role_xyz_' . time());
        $this->assertNull($result);
    }

    public function testRepositoryGetModelRolesReturnsArray(): void
    {
        $result = $this->roleRepo->getModelRoles('admin', 999999);
        $this->assertIsArray($result);
    }
}
