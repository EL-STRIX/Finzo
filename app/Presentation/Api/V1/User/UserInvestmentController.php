<?php

namespace App\Presentation\Api\V1\User;

use App\Application\Services\UserInvestmentService;
use App\Shared\Core\Exceptions\ApiException;

class UserInvestmentController
{
    private UserInvestmentService $service;

    public function __construct(UserInvestmentService $service)
    {
        $this->service = $service;
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
        $investments = $this->service->getUserInvestments($userId);
        return ['success' => true, 'investments' => $investments];
    }

    public function create(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }

        $amount = (float)($_POST['amount'] ?? 0);
        $tenure = (int)($_POST['tenure_months'] ?? 0);
        $autoRenew = (int)($_POST['auto_renew'] ?? 1);

        return $this->service->createFixedDeposit($userId, $amount, $tenure, $autoRenew);
    }

    public function close(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }
        $fdId = (int)($id ?? $_POST['fd_id'] ?? 0);
        return $this->service->closeFixedDeposit($userId, $fdId);
    }

    public function toggleAutoRenew(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }
        $fdId = (int)($id ?? $_POST['fd_id'] ?? 0);
        $autoRenew = (int)($_POST['auto_renew'] ?? 1);
        return $this->service->toggleAutoRenew($userId, $fdId, $autoRenew);
    }
}
