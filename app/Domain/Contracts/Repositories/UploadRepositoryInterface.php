<?php

namespace App\Domain\Contracts\Repositories;

interface UploadRepositoryInterface
{
    public function updateBranchCert(int $branchId, string $path): void;
    public function updateUserIdDocument(int $userId, string $path): void;
}
