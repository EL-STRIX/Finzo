<?php

namespace App\Domain\Contracts\Services;

interface StorageServiceInterface
{
    /**
     * Store an uploaded file and return its path.
     * 
     * @param array $file The $_FILES array item
     * @param string $destinationFolder The relative destination folder path
     * @param string|null $customFilename Optional custom filename
     * @return string The stored file path
     */
    public function store(array $file, string $destinationFolder, ?string $customFilename = null): string;

    /**
     * Check if a file exists.
     * 
     * @param string $path The relative file path
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * Delete a file.
     * 
     * @param string $path The relative file path
     * @return bool
     */
    public function delete(string $path): bool;
}
