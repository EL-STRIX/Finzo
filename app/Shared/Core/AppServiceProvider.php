<?php

namespace App\Shared\Core;

class AppServiceProvider
{
    public static function register(Container $container): void
    {
        // Repositories
        $container->bind(\App\Domain\Contracts\Repositories\UserRepositoryInterface::class, \App\Infrastructure\Repositories\UserRepository::class);
        $container->bind(\App\Domain\Contracts\Repositories\StaffRepositoryInterface::class, \App\Infrastructure\Repositories\StaffRepository::class);
        $container->bind(\App\Domain\Contracts\Repositories\AuthRepositoryInterface::class, \App\Infrastructure\Repositories\AuthRepository::class);
        $container->bind(\App\Domain\Contracts\Repositories\AdminManagementRepositoryInterface::class, \App\Infrastructure\Repositories\AdminManagementRepository::class);
        $container->bind(\App\Domain\Contracts\Repositories\AdminOperationsRepositoryInterface::class, \App\Infrastructure\Repositories\AdminOperationsRepository::class);
        $container->bind(\App\Domain\Contracts\Repositories\AdminStaffBranchRepositoryInterface::class, \App\Infrastructure\Repositories\AdminStaffBranchRepository::class);
        $container->bind(\App\Domain\Contracts\Repositories\PdfRepositoryInterface::class, \App\Infrastructure\Repositories\PdfRepository::class);
        $container->bind(\App\Domain\Contracts\Repositories\UploadRepositoryInterface::class, \App\Infrastructure\Repositories\UploadRepository::class);
        $container->bind(\App\Domain\Contracts\Repositories\LoanRepositoryInterface::class, \App\Infrastructure\Repositories\LoanRepository::class);

        // Security
        $container->bind(\App\Domain\Contracts\Security\SecurityServiceInterface::class, \App\Shared\Security\SecurityService::class);

        // Services
        $container->bind(\App\Domain\Contracts\Services\UserServiceInterface::class, \App\Application\Services\UserService::class);
        $container->bind(\App\Domain\Contracts\Services\StaffServiceInterface::class, \App\Application\Services\StaffService::class);
        $container->bind(\App\Domain\Contracts\Services\AuthServiceInterface::class, \App\Application\Services\AuthService::class);
        $container->bind(\App\Domain\Contracts\Services\AdminManagementServiceInterface::class, \App\Application\Services\AdminManagementService::class);
        $container->bind(\App\Domain\Contracts\Services\AdminOperationsServiceInterface::class, \App\Application\Services\AdminOperationsService::class);
        $container->bind(\App\Domain\Contracts\Services\AdminStaffBranchServiceInterface::class, \App\Application\Services\AdminStaffBranchService::class);
        $container->bind(\App\Domain\Contracts\Services\PdfServiceInterface::class, \App\Application\Services\PdfService::class);
        $container->bind(\App\Domain\Contracts\Services\UploadServiceInterface::class, \App\Application\Services\UploadService::class);
        $container->bind(\App\Domain\Contracts\Services\LoanServiceInterface::class, \App\Application\Services\LoanService::class);
    }
}
