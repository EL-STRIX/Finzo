<?php

namespace App\Infrastructure\Repositories;

use PDO;
use App\Shared\Core\Database;
use App\Domain\Contracts\Repositories\SettingsRepositoryInterface;

class SettingsRepository extends BaseRepository implements SettingsRepositoryInterface
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function getAllSettings(): array
    {
        $stmt = $this->db->query("SELECT * FROM system_settings ORDER BY setting_category ASC, label ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSettingsGroupedByCategory(): array
    {
        $settings = $this->getAllSettings();
        $grouped = [];
        
        foreach ($settings as $setting) {
            $category = $setting['setting_category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $setting;
        }
        
        return $grouped;
    }

    public function getSettingByKey(string $key): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $setting = $stmt->fetch(PDO::FETCH_ASSOC);
        return $setting ?: null;
    }

    public function updateSetting(string $key, string $value, ?int $updatedBy): bool
    {
        $stmt = $this->db->prepare("UPDATE system_settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?");
        return $stmt->execute([$value, $updatedBy, $key]);
    }
}
