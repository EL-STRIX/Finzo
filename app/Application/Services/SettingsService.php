<?php

namespace App\Application\Services;

use App\Domain\Contracts\Repositories\SettingsRepositoryInterface;
use App\Domain\Contracts\Services\SettingsServiceInterface;
use App\Shared\Core\Exceptions\ApiException;
use App\Domain\Exceptions\ValidationException;
use App\Shared\Core\Cache\CacheManager;
use App\Shared\Security\SecurityService;

class SettingsService implements SettingsServiceInterface
{
    private SettingsRepositoryInterface $repository;
    private CacheManager $cache;
    private SecurityService $security;

    public function __construct(SettingsRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->cache = CacheManager::getInstance();
        $this->security = new SecurityService();
    }

    public function getGroupedSettings(): array
    {
        return $this->repository->getSettingsGroupedByCategory();
    }

    public function getSetting(string $key)
    {
        $cacheKey = "system_setting_{$key}";
        $cachedValue = $this->cache->get($cacheKey);
        
        if ($cachedValue !== null) {
            return $cachedValue;
        }

        $setting = $this->repository->getSettingByKey($key);
        if (!$setting) {
            return null;
        }

        $value = $this->castValue($setting['setting_value'], $setting['setting_type']);
        
        // Cache for 24 hours
        $this->cache->set($cacheKey, $value, 86400);

        return $value;
    }

    public function updateSettings(array $settings, ?int $adminId = null): array
    {
        if (empty($settings)) {
            throw new ValidationException(json_encode(['settings' => 'No settings provided for update']));
        }

        $errors = [];
        $updatedCount = 0;

        foreach ($settings as $key => $value) {
            $settingDefinition = $this->repository->getSettingByKey($key);
            
            if (!$settingDefinition) {
                $errors[$key] = "Setting key not found";
                continue;
            }

            try {
                $validatedValue = $this->validateAndFormatValue($value, $settingDefinition['setting_type']);
                
                $success = $this->repository->updateSetting($key, $validatedValue, $adminId);
                
                if ($success) {
                    $this->cache->delete("system_setting_{$key}");
                    $updatedCount++;
                }
            } catch (\Exception $e) {
                $errors[$key] = $e->getMessage();
            }
        }

        if ($updatedCount > 0) {
            $this->security->writeAuditLog('settings_updated', null, 'system', $adminId, 'admin', "Updated $updatedCount settings");
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Some settings failed to update',
                'updated_count' => $updatedCount,
                'errors' => $errors
            ];
        }

        return [
            'success' => true,
            'message' => 'Settings updated successfully',
            'updated_count' => $updatedCount
        ];
    }

    private function validateAndFormatValue($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                
            case 'number':
                if (!is_numeric($value)) {
                    throw new \Exception("Value must be a number");
                }
                return (string)$value;
                
            case 'json':
                if (is_array($value) || is_object($value)) {
                    return json_encode($value);
                }
                json_decode($value);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception("Value must be valid JSON");
                }
                return $value;
                
            case 'string':
            default:
                return trim((string)$value);
        }
    }

    private function castValue(string $value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return strpos($value, '.') !== false ? (float)$value : (int)$value;
            case 'json':
                return json_decode($value, true);
            case 'string':
            default:
                return $value;
        }
    }
}
