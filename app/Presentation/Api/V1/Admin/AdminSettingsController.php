<?php

namespace App\Presentation\Api\V1\Admin;

use App\Domain\Contracts\Services\SettingsServiceInterface;
use App\Shared\Security\SecurityService;

class AdminSettingsController
{
    private SettingsServiceInterface $service;
    private SecurityService $security;

    public function __construct(SettingsServiceInterface $service, ?SecurityService $security = null)
    {
        $this->service = $service;
        $this->security = $security ?? new SecurityService();
    }

    public function index(): array
    {
        $this->security->enforcePermission('manage_settings');
        return ['success' => true, 'data' => $this->service->getGroupedSettings()];
    }

    public function update(): array
    {
        $this->security->enforcePermission('manage_settings');
        
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

        $settings = $input['settings'] ?? [];
        $adminId = \App\Shared\Security\Auth::user()['id'] ?? null;
        
        $result = $this->service->updateSettings($settings, $adminId);
        
        if (!$result['success']) {
            http_response_code(422);
        }
        
        return $result;
    }

    public function triggerBackup(): array
    {
        $this->security->enforcePermission('manage_settings');
        
        $type = $_POST['type'] ?? 'full';
        if (!in_array($type, ['full', 'schema', 'data'])) {
            $type = 'full';
        }

        try {
            $bcdr = new \Database\BcdrManager(\App\Shared\Core\Database::getInstance());
            $bcdr->backup($type);
            
            $adminId = \App\Shared\Security\Auth::user()['id'] ?? null;
            $this->security->writeAuditLog('backup_triggered', null, 'system', $adminId, 'admin', "Triggered $type backup");
            
            return ['success' => true, 'message' => 'Database backup triggered successfully'];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['success' => false, 'message' => 'Failed to trigger backup: ' . $e->getMessage()];
        }
    }
}
