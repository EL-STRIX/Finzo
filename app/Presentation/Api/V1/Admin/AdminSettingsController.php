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

    private function checkAccess(): void
    {
        $user = \App\Shared\Security\Auth::user();
        if (($user['role'] ?? '') === 'super_admin' || ($user['admin_role'] ?? '') === 'super_admin') {
            return;
        }

        try {
            $this->security->enforcePermission('manage_admins');
        } catch (\Throwable $e) {
            $this->security->enforcePermission('manage_settings');
        }
    }

    private function parseInput(): array
    {
        $input = $_POST;
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $input = array_merge($input, $decoded);
            } else {
                parse_str($raw, $parsed);
                if (is_array($parsed)) {
                    $input = array_merge($input, $parsed);
                }
            }
        }
        return $input;
    }

    public function index(): array
    {
        $this->checkAccess();
        return ['success' => true, 'data' => $this->service->getGroupedSettings()];
    }

    public function update(): array
    {
        $this->checkAccess();
        
        $input = $this->parseInput();
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
        $this->checkAccess();
        
        $input = $this->parseInput();
        $type = $input['type'] ?? 'full';
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
