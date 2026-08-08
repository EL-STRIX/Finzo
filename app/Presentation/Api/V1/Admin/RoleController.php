<?php

namespace App\Presentation\Api\V1\Admin;

use App\Domain\Contracts\Services\RoleServiceInterface;
use App\Shared\Security\SecurityService;

class RoleController
{
    private RoleServiceInterface $service;
    private SecurityService $security;

    public function __construct(RoleServiceInterface $service, ?SecurityService $security = null)
    {
        $this->service = $service;
        $this->security = $security ?? new SecurityService();
    }

    public function index(): array
    {
        $this->security->enforcePermission('manage_roles');
        return ['success' => true, 'data' => $this->service->getRoles()];
    }

    public function show(?string $id = null): array
    {
        $this->security->enforcePermission('manage_roles');
        $roleId = (int)($id ?? $_GET['id'] ?? 0);
        return ['success' => true, 'data' => $this->service->getRoleDetails($roleId)];
    }

    public function create(): array
    {
        $this->security->enforcePermission('manage_roles');
        $is_json = (\App\Shared\Core\Validation\Validator::isSet($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
        if ($is_json) {
            $json_input = json_decode(file_get_contents('php://input'), true);
            if ($json_input !== null) {
                $_POST = array_merge($_POST, $json_input);
            }
        }

        $result = $this->service->createRole($_POST);
        http_response_code(201);
        return ['success' => true, 'message' => 'Role created successfully', 'data' => $result];
    }

    public function update(?string $id = null): array
    {
        $this->security->enforcePermission('manage_roles');
        $roleId = (int)($id ?? $_GET['id'] ?? 0);
        
        $is_json = (\App\Shared\Core\Validation\Validator::isSet($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
        $input = $_POST;
        if ($is_json) {
            $json_input = json_decode(file_get_contents('php://input'), true);
            if ($json_input !== null) {
                $input = array_merge($input, $json_input);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
            parse_str(file_get_contents("php://input"), $put_vars);
            $input = array_merge($input, $put_vars);
        }

        $result = $this->service->updateRole($roleId, $input);
        return ['success' => true, 'message' => 'Role updated successfully', 'data' => $result];
    }

    public function destroy(?string $id = null): array
    {
        $this->security->enforcePermission('manage_roles');
        $roleId = (int)($id ?? $_GET['id'] ?? 0);
        return $this->service->deleteRole($roleId);
    }

    public function permissions(): array
    {
        $this->security->enforcePermission('manage_roles');
        return ['success' => true, 'data' => $this->service->getPermissions()];
    }

    public function assignRoles(?string $modelType = null, ?string $modelId = null): array
    {
        $this->security->enforcePermission('manage_roles');
        
        $is_json = (\App\Shared\Core\Validation\Validator::isSet($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
        $input = $_POST;
        if ($is_json) {
            $json_input = json_decode(file_get_contents('php://input'), true);
            if ($json_input !== null) {
                $input = array_merge($input, $json_input);
            }
        }
        
        $type = $this->security->cleanInput($modelType ?? $input['model_type'] ?? '');
        $id = (int)($modelId ?? $input['model_id'] ?? 0);
        $roleIds = $input['role_ids'] ?? [];
        
        if (!is_array($roleIds)) {
            $roleIds = [$roleIds];
        }
        
        return $this->service->syncModelRoles($type, $id, $roleIds);
    }
}
