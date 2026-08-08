<?php

namespace App\Domain\Contracts\Services;

interface UploadServiceInterface
{
    public function handleBranchCertUpload(array $file, int $branchId): array;
    public function handleIdDocumentUpload(array $file, int $userId): array;
}
