<?php
namespace App\Infrastructure\Repositories;

class UploadRepository extends BaseRepository implements \App\Domain\Contracts\Repositories\UploadRepositoryInterface
{
    public function updateBranchCert(int $branchId, string $path): void
    {
        $stmt = $this->pdo->prepare("UPDATE branches SET cert_path = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$path, $branchId]);
    }

    public function updateUserIdDocument(int $userId, string $path): void
    {
        $stmt = $this->pdo->prepare("UPDATE users SET id_document_path = ?, kyc_status = 'under_review', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$path, $userId]);
    }
}
