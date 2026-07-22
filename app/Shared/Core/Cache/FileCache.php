<?php
namespace App\Shared\Core\Cache;

class FileCache
{
    private static function getPath(string $key): string
    {
        $path = FINZO_ROOT . '/storage/framework/cache/data';
        if (!is_dir($path)) mkdir($path, 0777, true);
        return $path . '/' . md5($key) . '.json';
    }

    public static function remember(string $key, int $ttlSeconds, callable $callback)
    {
        $file = self::getPath($key);
        
        if (file_exists($file) && (time() - filemtime($file) < $ttlSeconds)) {
            $data = json_decode(file_get_contents($file), true);
            if ($data !== null) {
                return $data['payload'];
            }
        }
        
        $payload = $callback();
        
        // Atomic write
        $temp = $file . '.' . uniqid('', true);
        file_put_contents($temp, json_encode(['payload' => $payload], JSON_UNESCAPED_UNICODE));
        rename($temp, $file);
        
        return $payload;
    }

    public static function forget(string $key): void
    {
        $file = self::getPath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }
}