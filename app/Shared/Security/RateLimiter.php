<?php
namespace App\Shared\Security;

use App\Shared\Core\Exceptions\ApiException;

class RateLimiter
{
    private static function getStoragePath(): string
    {
        $path = FINZO_ROOT . '/storage/framework/cache';
        if (!is_dir($path)) mkdir($path, 0777, true);
        return $path . '/rate_limits.json';
    }

    public static function check(string $key, int $maxRequests, int $decayMinutes): void
    {
        $file = self::getStoragePath();
        
        // Simple file-based atomic tracking (Enterprise would use Redis, but avoiding infrastructure changes)
        $fp = fopen($file, 'c+');
        if (flock($fp, LOCK_EX)) {
            $data = json_decode(stream_get_contents($fp) ?: '{}', true) ?? [];
            if (!is_array($data)) {
                $data = [];
            }
            
            $now = time();
            $windowStart = $now - ($decayMinutes * 60);

            // Clean up old entries
            foreach ($data as $k => $requests) {
                $data[$k] = array_filter($requests, fn($time) => $time > $windowStart);
                if (empty($data[$k])) {
                    unset($data[$k]);
                }
            }

            // Check current key
            if (!isset($data[$key])) $data[$key] = [];
            
            if (count($data[$key]) >= $maxRequests) {
                flock($fp, LOCK_UN);
                fclose($fp);
                throw new ApiException('Too Many Requests', 429);
            }

            $data[$key][] = $now;

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data));
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
}