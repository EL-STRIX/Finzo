<?php

namespace App\Shared\Core\Cache;

class CacheManager
{
    private static ?CacheManager $instance = null;
    private array $memoryCache = [];
    private string $cacheDir;

    private function __construct()
    {
        $this->cacheDir = dirname(__DIR__, 4) . '/storage/cache/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public static function getInstance(): CacheManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $key, callable $default, int $ttl = 3600)
    {
        if (isset($this->memoryCache[$key])) {
            return $this->memoryCache[$key];
        }

        $file = $this->cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires_at'] > time()) {
                $this->memoryCache[$key] = $data['value'];
                return $data['value'];
            }
            unlink($file);
        }

        $value = $default();
        $this->set($key, $value, $ttl);
        return $value;
    }

    public function set(string $key, $value, int $ttl = 3600): void
    {
        $this->memoryCache[$key] = $value;
        $file = $this->cacheDir . md5($key) . '.cache';
        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl
        ];
        file_put_contents($file, serialize($data));
    }

    public function forget(string $key): void
    {
        unset($this->memoryCache[$key]);
        $file = $this->cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
