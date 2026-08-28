<?php

namespace App\Presentation\Api\V1\User;

use App\Application\Services\UserBillPaymentService;
use App\Shared\Core\Exceptions\ApiException;

class UserBillPaymentController
{
    private UserBillPaymentService $service;

    public function __construct(UserBillPaymentService $service)
    {
        $this->service = $service;
    }

    public function billers(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }
        return ['success' => true, 'billers' => $this->service->getBillers($userId)];
    }

    public function history(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }
        return ['success' => true, 'history' => $this->service->getHistory($userId)];
    }

    public function pay(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }

        $category = (string)($_POST['category'] ?? 'Utility');
        $billerName = (string)($_POST['biller_name'] ?? '');
        $consumerNumber = (string)($_POST['consumer_number'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);

        return $this->service->payBill($userId, $category, $billerName, $consumerNumber, $amount);
    }
}
