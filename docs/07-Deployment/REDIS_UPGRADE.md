# Redis Upgrade Path for CacheManager & QueueManager

Currently, Finzo utilizes file-based caching and database-backed queues for simplicity and ease of deployment. However, as the enterprise scales, upgrading to Redis is highly recommended for performance, concurrency, and reliability.

## 1. Prerequisites
- A running Redis server (version 6.2+)
- The `phpredis` extension installed and enabled in `php.ini` (`extension=redis`)

## 2. Configuration Changes
Update your `.env` file with the following settings:
```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 3. Implementation Steps

### CacheManager Update
Update `App\Shared\Core\CacheManager.php` to instantiate a Redis client when `CACHE_DRIVER === 'redis'`:

```php
private function initRedis() {
    $this->redis = new \Redis();
    $this->redis->connect(REDIS_HOST, REDIS_PORT);
    if (REDIS_PASSWORD) {
        $this->redis->auth(REDIS_PASSWORD);
    }
}
```

### QueueManager Update
Modify `App\Shared\Core\Queue\QueueManager.php` to push jobs to a Redis list (`rPush` and `blPop`) instead of inserting into the `jobs` MySQL table.

## 4. Considerations
- Redis operates in-memory, ensuring O(1) read/write operations for high-throughput environments.
- Ensure Redis is properly secured with strong passwords and bound only to private network interfaces.
- Configure Redis eviction policies (`maxmemory-policy allkeys-lru`) to prevent out-of-memory errors on the cache DB.
