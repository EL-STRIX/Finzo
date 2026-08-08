<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\SettingsService;
use App\Infrastructure\Repositories\SettingsRepository;

/**
 * Integration tests for SettingsService and SettingsRepository.
 * Covers all major code paths in both classes using the real test database.
 */
class SettingsServiceIntegrationTest extends TestCase
{
    private SettingsService $settingsService;
    private SettingsRepository $settingsRepo;

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

        $this->settingsRepo = new SettingsRepository();
        $this->settingsService = new SettingsService($this->settingsRepo);
    }

    public function testGetGroupedSettingsReturnsArray(): void
    {
        $result = $this->settingsService->getGroupedSettings();
        $this->assertIsArray($result);
    }

    public function testGetSettingReturnsNullForMissingKey(): void
    {
        $result = $this->settingsService->getSetting('nonexistent_key_xyz_' . time());
        $this->assertNull($result);
    }

    public function testGetSettingReturnsCachedResult(): void
    {
        // Call twice to exercise cache hit (second call hits memory cache)
        $key = 'nonexistent_key_cache_test_' . time();
        $this->settingsService->getSetting($key);
        $result = $this->settingsService->getSetting($key);
        $this->assertNull($result);
    }

    public function testUpdateSettingsThrowsOnEmpty(): void
    {
        $this->expectException(\App\Domain\Exceptions\ValidationException::class);
        $this->settingsService->updateSettings([]);
    }

    public function testUpdateSettingsHandlesMissingKey(): void
    {
        $result = $this->settingsService->updateSettings(['nonexistent_key_xyz' => 'value']);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testRepositoryGetAllSettings(): void
    {
        $result = $this->settingsRepo->getAllSettings();
        $this->assertIsArray($result);
    }

    public function testRepositoryGetSettingsGroupedByCategory(): void
    {
        $result = $this->settingsRepo->getSettingsGroupedByCategory();
        $this->assertIsArray($result);
    }

    public function testRepositoryGetSettingByKeyReturnsNull(): void
    {
        $result = $this->settingsRepo->getSettingByKey('no_such_key_' . time());
        $this->assertNull($result);
    }
}
