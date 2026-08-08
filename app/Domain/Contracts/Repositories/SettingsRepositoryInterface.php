<?php

namespace App\Domain\Contracts\Repositories;

interface SettingsRepositoryInterface
{
    /**
     * Retrieve all settings
     * @return array
     */
    public function getAllSettings(): array;
    
    /**
     * Retrieve settings grouped by their category
     * @return array
     */
    public function getSettingsGroupedByCategory(): array;

    /**
     * Retrieve a specific setting by key
     * @param string $key
     * @return array|null
     */
    public function getSettingByKey(string $key): ?array;

    /**
     * Update a specific setting
     * @param string $key
     * @param string $value
     * @param int|null $updatedBy
     * @return bool
     */
    public function updateSetting(string $key, string $value, ?int $updatedBy): bool;
}
