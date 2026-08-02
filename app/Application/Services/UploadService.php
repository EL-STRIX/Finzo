<?php
namespace App\Application\Services;

use App\Infrastructure\Repositories\UploadRepository;
use App\Shared\Core\Logging\Logger;

class UploadService implements \App\Domain\Contracts\Services\UploadServiceInterface
{
    private UploadRepository $repository;
    /**
     *   construct.
     *
     * @param \App\Domain\Contracts\Repositories\UploadRepositoryInterface $repository
     * @param ?\App\Domain\Contracts\Security\SecurityServiceInterface $security
     */
    public function __construct(\App\Domain\Contracts\Repositories\UploadRepositoryInterface $repository, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->repository = $repository;
    }
    /**
     * Handle Branch Cert Upload.
     *
     * @param array $file
     * @param int $branchId
     * @return array
     */
    public function handleBranchCertUpload(array $file, int $branchId): array
    {
        if ($file['size'] > 5242880) return ['success' => false, 'message' => 'File size must be less than 5MB', 'status' => 422];
        
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);

        if (!in_array($mime, $allowed_types)) return ['success' => false, 'message' => 'Only JPG, PNG, and PDF files are allowed', 'status' => 422];

        if (!is_dir(FINZO_ROOT . '/uploads/branch-certs/')) {
            mkdir(FINZO_ROOT . '/uploads/branch-certs/', 0755, true);
        }

        $ext_map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
        $ext = $ext_map[$mime] ?? 'bin';
        $filename = 'branch_cert_' . ($branchId ?: 'new') . '_' . time() . '.' . $ext;
        $destination = FINZO_ROOT . '/uploads/branch-certs/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'message' => 'Failed to save certificate file', 'status' => 500];
        }

        $relative_path = 'uploads/branch-certs/' . $filename;

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
     * @param array $file
     * @param int $userId
     * @return array
     */
    public function handleIdDocumentUpload(array $file, int $userId): array
    {
        if ($file['size'] > 5242880) return ['success' => false, 'message' => 'File size must be less than 5MB', 'status' => 422];
        
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) return ['success' => false, 'message' => 'Only JPG, PNG, and PDF files are allowed', 'status' => 422];

        if (!is_dir(FINZO_ROOT . '/uploads/kyc/')) {
            mkdir(FINZO_ROOT . '/uploads/kyc/', 0755, true);
        }

        $ext_map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
        $ext = $ext_map[$mime] ?? 'bin';
        $filename = 'id_doc_' . $userId . '_' . time() . '.' . $ext;
        $destination = FINZO_ROOT . '/uploads/kyc/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'message' => 'Failed to save document file', 'status' => 500];
        }

        $relative_path = 'uploads/kyc/' . $filename;

        try {
            $this->repository->updateUserIdDocument($userId, $relative_path);
        } catch (\PDOException $e) {
            Logger::getInstance()->error('ID Doc upload DB error: ', ['context' => $e->getMessage()]);
        }

        return ['success' => true, 'message' => 'Document uploaded successfully', 'path' => $relative_path, 'filename' => $filename, 'status' => 200];
    }
}