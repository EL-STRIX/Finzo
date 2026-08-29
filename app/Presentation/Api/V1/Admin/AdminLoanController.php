<?php

namespace App\Presentation\Api\V1\Admin;

use App\Domain\Contracts\Services\LoanServiceInterface;
use App\Domain\Contracts\Security\SecurityServiceInterface;
use App\Shared\Core\Exceptions\ApiException;

class AdminLoanController
{
    private LoanServiceInterface $service;
    private SecurityServiceInterface $security;

    public function __construct(LoanServiceInterface $service, ?SecurityServiceInterface $security = null)
    {
        $this->service = $service;
        if ($security) {
            $this->security = $security;
        } else {
            $this->security = \App\Shared\Core\Container::getInstance()->resolve(SecurityServiceInterface::class);
        }
    }

    public function products(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $status = $this->security->cleanInput($_GET['status'] ?? 'all');
        $products = $this->service->getLoanProducts($status !== '' ? $status : 'all');

        // Normalize for the admin UI: status enum -> is_active boolean
        $products = array_map(function ($p) {
            $p['is_active'] = (($p['status'] ?? '') === 'active');
            return $p;
        }, $products);

        return ['success' => true, 'data' => $products, 'products' => $products];
    }

    public function createProduct(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'interest_rate' => $_POST['interest_rate'] ?? '',
            'min_amount' => $_POST['min_amount'] ?? '',
            'max_amount' => $_POST['max_amount'] ?? '',
            'min_tenure_months' => $_POST['min_tenure_months'] ?? '',
            'max_tenure_months' => $_POST['max_tenure_months'] ?? '',
        ];

        return $this->service->createLoanProduct($data);
    }

    public function applications(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $status = $_GET['status'] ?? 'all';
        $apps = $this->service->getLoanApplications(['status' => $status]);
        return ['success' => true, 'applications' => $apps];
    }

    public function approveApplication(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $appId = (int)($id ?? $_POST['id'] ?? 0);
        $adminId = $_SESSION['admin_id'] ?? 0;
        $notes = $this->security->cleanInput($_POST['notes'] ?? '');
        
        return $this->service->approveApplication($appId, $adminId, $notes);
    }

    public function rejectApplication(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $appId = (int)($id ?? $_POST['id'] ?? 0);
        $adminId = $_SESSION['admin_id'] ?? 0;
        $notes = $this->security->cleanInput($_POST['notes'] ?? '');
        
        return $this->service->rejectApplication($appId, $adminId, $notes);
    }

    public function schedules(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        
        $loanId = (int)($id ?? $_GET['loan_id'] ?? 0);
        $schedules = $this->service->getLoanSchedules($loanId);
        
        return ['success' => true, 'schedules' => $schedules];
    }

    public function foreclose(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        
        $loanId = (int)($id ?? $_POST['loan_id'] ?? 0);
        $adminId = $_SESSION['admin_id'] ?? 0;
        $penaltyRate = (float)($_POST['penalty_rate'] ?? 2.0); // Default 2% penalty
        
        return $this->service->forecloseLoan($loanId, $adminId, $penaltyRate);
    }
}
