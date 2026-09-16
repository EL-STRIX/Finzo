<?php

namespace Tests\Unit\Core;

use Tests\TestCase;
use App\Shared\Security\RateLimiter;
use App\Shared\Core\Exceptions\ApiException;

class RateLimiterTest extends TestCase
{
    private string $testKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testKey = 'test_unit_rate_key_' . uniqid();
    }

    public function testAllowsRequestsUnderLimit(): void
    {
        RateLimiter::check($this->testKey, 5, 1);
        RateLimiter::check($this->testKey, 5, 1);
        RateLimiter::check($this->testKey, 5, 1);

        $this->assertTrue(true);
    }

    public function testBlocksRequestsExceedingLimit(): void
    {
        $limitKey = 'block_key_' . uniqid();
        RateLimiter::check($limitKey, 2, 1);
        RateLimiter::check($limitKey, 2, 1);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(429);
        RateLimiter::check($limitKey, 2, 1);
    }
}
