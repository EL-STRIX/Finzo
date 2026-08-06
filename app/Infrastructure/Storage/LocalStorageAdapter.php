<?php

namespace App\Infrastructure\Storage;

use App\Domain\Contracts\Services\StorageServiceInterface;
use Exception;

class LocalStorageAdapter implements StorageServiceInterface
{
    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath ?: UPLOAD_PATH, '/\\') . '/';
    }

    public function store(array $file, string $destinationFolder, ?string $customFilename = null): string
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception("Invalid file upload.");
        }

        $folderPath = $this->basePath . rtrim($destinationFolder, '/\\') . '/';
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        $filename = $customFilename ?: uniqid() . '_' . basename($file['name']);
        $destination = $folderPath . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Failed to move uploaded file.");
        }

        // Return relative path for database storage (e.g., 'public/uploads/folder/filename.ext')
        // Assume all storage starts at project root.
        $relativePath = str_replace(FINZO_ROOT . '/', '', $destination);
        // Standardize to forward slashes
        return str_replace('\\', '/', $relativePath);
    }

    public function exists(string $path): bool
    {
        return file_exists(FINZO_ROOT . '/' . ltrim($path, '/\\'));
    }

    public function delete(string $path): bool
    {
        $fullPath = FINZO_ROOT . '/' . ltrim($path, '/\\');
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}
