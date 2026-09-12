<?php
namespace App\Shared\Security;

use App\Shared\Core\Exceptions\ApiException;

class RateLimiter
{
    private static function getStoragePath(): string
    {
        $root = defined('FINZO_ROOT') ? FINZO_ROOT : dirname(__DIR__, 3);
        $path = $root . '/storage/framework/cache';
        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }
        return $path . '/rate_limits.json';
    }

    public static function check(string $key, int $maxRequests, int $decayMinutes): void
    {
        $file = self::getStoragePath();
        
        $fp = @fopen($file, 'c+');
        if (!$fp) {
            return; // Fallback if file storage is temporarily unavailable
        }

        if (flock($fp, LOCK_EX)) {
            $content = stream_get_contents($fp);
            $data = json_decode($content ?: '{}', true) ?? [];
            if (!is_array($data)) {
                $data = [];
            }
            
            $now = time();
            $windowStart = $now - ($decayMinutes * 60);

            // Clean up old entries across all keys
            foreach ($data as $k => $requests) {
                if (is_array($requests)) {
                    $data[$k] = array_values(array_filter($requests, fn($time) => is_numeric($time) && $time > $windowStart));
                    if (empty($data[$k])) {
                        unset($data[$k]);
                    }
                } else {
                    unset($data[$k]);
                }
            }

            // Check current key
            if (!isset($data[$key]) || !is_array($data[$key])) {
                $data[$key] = [];
            }
            
            $currentCount = count($data[$key]);
            $remaining = max(0, $maxRequests - $currentCount);
            $resetTime = $now + ($decayMinutes * 60);

            if (!headers_sent()) {
                header("X-RateLimit-Limit: {$maxRequests}");
                header("X-RateLimit-Remaining: {$remaining}");
                header("X-RateLimit-Reset: {$resetTime}");
            }

            if ($currentCount >= $maxRequests) {
                flock($fp, LOCK_UN);
                fclose($fp);
                if (!headers_sent()) {
                    header("Retry-After: " . ($decayMinutes * 60));
                }
                throw new ApiException('Too Many Requests. Please wait 1 minute before trying again.', 429);
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