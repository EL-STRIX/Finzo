<?php
namespace App\Application\Services;

use App\Infrastructure\Repositories\UploadRepository;
use App\Shared\Core\Logging\Logger;

class UploadService implements \App\Domain\Contracts\Services\UploadServiceInterface
{
    private UploadRepository $repository;
    private \App\Shared\Core\Storage\StorageServiceInterface $storage;

    public function __construct(
        \App\Domain\Contracts\Repositories\UploadRepositoryInterface $repository,
        ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null,
        ?\App\Shared\Core\Storage\StorageServiceInterface $storage = null
    ) {
        $this->repository = $repository;
        $this->storage = $storage ?? new \App\Shared\Core\Storage\LocalStorageService();
    }
    /**
     * Handle Branch Cert Upload.
     *
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file $_FILES array payload
     * @param int $branchId The ID of the branch
     * @return array{success: bool, message: string, status: int, path?: string, filename?: string}
     */
    public function handleBranchCertUpload(array $file, int $branchId): array
    {
        if ($file['size'] > 5242880) return ['success' => false, 'message' => 'File size must be less than 5MB', 'status' => 422];
        
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);

        if (!in_array($mime, $allowed_types)) return ['success' => false, 'message' => 'Only JPG, PNG, and PDF files are allowed', 'status' => 422];

        $ext_map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
        $ext = $ext_map[$mime] ?? 'bin';
        $filename = 'branch_cert_' . ($branchId ?: 'new') . '_' . time() . '.' . $ext;

        try {
            $relative_path = $this->storage->store($file['tmp_name'], 'branch-certs', $filename);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to save certificate file', 'status' => 500];
        }

        if ($branchId > 0) {
            try {
                $this->repository->updateBranchCert($branchId, $relative_path);
            } catch (\PDOException $e) {
                Logger::getInstance()->error('Branch cert upload DB error: ', ['context' => $e->getMessage()]);
            }
        }

        return ['success' => true, 'message' => 'Certificate uploaded successfully', 'path' => $relative_path, 'filename' => $filename, 'status' => 200];
    }
    /**
     * Handle Id Document Upload.
     *
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file $_FILES array payload
     * @param int $userId The ID of the user uploading the document
     * @return array{success: bool, message: string, status: int, path?: string, filename?: string}
     */
    public function handleIdDocumentUpload(array $file, int $userId): array
    {
        if ($file['size'] > 5242880) return ['success' => false, 'message' => 'File size must be less than 5MB', 'status' => 422];
        
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) return ['success' => false, 'message' => 'Only JPG, PNG, and PDF files are allowed', 'status' => 422];

        $ext_map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
        $ext = $ext_map[$mime] ?? 'bin';
        $filename = 'id_doc_' . $userId . '_' . time() . '.' . $ext;

        try {
            $relative_path = $this->storage->store($file['tmp_name'], 'kyc', $filename);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to save document file', 'status' => 500];
        }

        try {
            $this->repository->updateUserIdDocument($userId, $relative_path);
        } catch (\PDOException $e) {
            Logger::getInstance()->error('ID Doc upload DB error: ', ['context' => $e->getMessage()]);
        }

        return ['success' => true, 'message' => 'Document uploaded successfully', 'path' => $relative_path, 'filename' => $filename, 'status' => 200];
    }
}