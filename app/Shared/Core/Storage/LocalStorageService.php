<?php
namespace App\Shared\Core\Storage;

class LocalStorageService implements StorageServiceInterface
{
    private string $baseDir;

    public function __construct()
    {
        $this->baseDir = defined('FINZO_ROOT') ? FINZO_ROOT : dirname(__DIR__, 4);
    }

    public function store(string $tempPath, string $directory, string $filename): string
    {
        $targetDir = $this->baseDir . '/uploads/' . $directory;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $destination = $targetDir . '/' . $filename;
        if (!move_uploaded_file($tempPath, $destination)) {
            throw new \Exception("Failed to move uploaded file to {$destination}");
        }

        return 'uploads/' . $directory . '/' . $filename;
    }

    public function getUrl(string $relativePath): string
    {
        // For local storage, we just return the relative path from public root
        return '/' . $relativePath;
    }
}
