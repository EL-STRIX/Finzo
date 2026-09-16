# Redis Upgrade Path for Cache & Queue Scaling

Finzo supports file-based caching and database-backed queues by default for ease of local deployment. In high-concurrency production installations, upgrading to Redis delivers sub-millisecond atomic caching, high-throughput session stores, and low-latency background job processing.

---

## 1. Prerequisites
- Running Redis Server (v6.2 or higher).
- PHP `redis` extension enabled (`sudo apt install php8.4-redis` or `extension=redis` in `php.ini`).

---

## 2. Configuration Settings

Update `/var/www/finzo/.env` with the following variables:

```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=YOUR_SECURE_REDIS_PASSWORD
REDIS_DATABASE=0
REDIS_CACHE_DATABASE=1
```

---

## 3. Implementation Verification

Once enabled, `App\Shared\Core\CacheManager` and `App\Shared\Core\QueueManager` automatically detect `CACHE_DRIVER=redis` and route cache reads/writes to the Redis instance:

```bash
# Test Redis connectivity from Finzo CLI
php -r "require 'bootstrap/app.php'; \$c = \App\Shared\Core\Container::getInstance()->get(\App\Domain\Contracts\Services\CacheServiceInterface::class); \$c->set('test_key', 'ok', 60); echo 'Redis Test: ' . \$c->get('test_key') . PHP_EOL;"
```

---

## 4. Production Security Considerations
1. **Network Binding:** Bind Redis only to localhost (`127.0.0.1`) or a secure private VPC network interface. Never expose port 6379 to the public Internet.
2. **Authentication:** Enforce a high-entropy password via `requirepass` in `/etc/redis/redis.conf`.
3. **Eviction Policy:** Configure `maxmemory-policy allkeys-lru` to allow automatic pruning of least-recently-used cache entries when memory limits are reached.
