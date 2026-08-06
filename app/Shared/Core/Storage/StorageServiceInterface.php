<?php
namespace App\Shared\Core\Storage;

interface StorageServiceInterface
{
    /**
     * Store a file and return the relative path.
     *
     * @param string $tempPath The temporary path of the uploaded file
     * @param string $directory The directory to store the file in (e.g., 'kyc', 'branch-certs')
     * @param string $filename The desired filename
     * @return string The relative path to the stored file
     */
    public function store(string $tempPath, string $directory, string $filename): string;

    /**
     * Get the full absolute path or URL for a given relative path.
     *
     * @param string $relativePath
     * @return string
     */
    public function getUrl(string $relativePath): string;
}
