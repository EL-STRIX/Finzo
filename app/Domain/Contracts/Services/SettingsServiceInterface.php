<?php

namespace App\Domain\Contracts\Services;

interface SettingsServiceInterface
{
    /**
     * Retrieve all settings grouped by category
     * @return array
     */
    public function getGroupedSettings(): array;

    /**
     * Retrieve a specific setting by key (fetches from cache if available)
     * @param string $key
     * @return mixed
     */
    public function getSetting(string $key);

    /**
     * Mass update settings
     * @param array $settings Key-Value pairs of settings to update
     * @param int|null $adminId
     * @return array Result message
     */
    public function updateSettings(array $settings, ?int $adminId = null): array;
}
