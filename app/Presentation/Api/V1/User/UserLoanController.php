<?php

namespace App\Presentation\Api\V1\User;

use App\Domain\Contracts\Services\LoanServiceInterface;
use App\Domain\Contracts\Security\SecurityServiceInterface;
use App\Shared\Core\Exceptions\ApiException;

class UserLoanController
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

    public function list(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }

        $loans = $this->service->getLoanApplications(['user_id' => $userId]);
        return ['success' => true, 'loans' => $loans];
    }

    public function products(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $products = $this->service->getLoanProducts('active');
        return ['success' => true, 'products' => $products];
    }

    public function apply(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }

        $data = [
            'loan_product_id' => $_POST['loan_product_id'] ?? '',
            'amount' => $_POST['amount'] ?? '',
            'tenure_months' => $_POST['tenure_months'] ?? '',
            'purpose' => $this->security->cleanInput($_POST['purpose'] ?? '')
        ];

        return $this->service->applyForLoan($userId, $data);
    }

    public function schedules(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        
        // Use path parameter if provided, fallback to query param
        $loanId = (int)($id ?? $_GET['loan_id'] ?? 0);
        $schedules = $this->service->getLoanSchedules($loanId);
        
        return ['success' => true, 'schedules' => $schedules];
    }

    public function repay(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }
        
        $loanId = (int)($id ?? $_POST['loan_id'] ?? 0);
        $scheduleId = (int)($_POST['schedule_id'] ?? 0);

        return $this->service->processRepayment($userId, $loanId, $scheduleId);
    }

    public function repayments(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        
        $loanId = (int)($id ?? $_GET['loan_id'] ?? 0);
        $repayments = $this->service->getLoanRepayments($loanId);
        
        return ['success' => true, 'repayments' => $repayments];
    }
}
