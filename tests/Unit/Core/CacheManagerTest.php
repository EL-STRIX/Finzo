<?php

namespace Tests\Unit\Core;

use Tests\TestCase;
use App\Shared\Core\Cache\CacheManager;

class CacheManagerTest extends TestCase
{
    private CacheManager $cache;
    private string $key;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = CacheManager::getInstance();
        $this->key = 'unit_test_key_' . uniqid();
    }

    protected function tearDown(): void
    {
        $this->cache->forget($this->key);
        parent::tearDown();
    }

    public function testGetEvaluatesDefaultWhenMissing(): void
    {
        $called = false;
        $val = $this->cache->get($this->key, function () use (&$called) {
            $called = true;
            return 'computed_val_123';
        }, 300);

        $this->assertTrue($called);
        $this->assertEquals('computed_val_123', $val);
    }

    public function testSetStoresAndReturnsCachedValue(): void
    {
        $this->cache->set($this->key, ['status' => 'active', 'balance' => 9500]);

        $called = false;
        $val = $this->cache->get($this->key, function () use (&$called) {
            $called = true;
            return ['status' => 'stale'];
        });

        $this->assertFalse($called);
        $this->assertEquals(['status' => 'active', 'balance' => 9500], $val);
    }

    public function testForgetRemovesValue(): void
    {
        $this->cache->set($this->key, 'original_data');
        $this->cache->forget($this->key);

        $called = false;
        $val = $this->cache->get($this->key, function () use (&$called) {
            $called = true;
            return 'fresh_data';
        });

        $this->assertTrue($called);
        $this->assertEquals('fresh_data', $val);
    }
}
