<?php

namespace App\Presentation\Api\V1\User;

use App\Application\Services\UserSecurityService;
use App\Shared\Core\Exceptions\ApiException;

class UserSecurityCenterController
{
    private UserSecurityService $service;

    public function __construct(UserSecurityService $service)
    {
        $this->service = $service;
    }

    public function overview(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }
        return ['success' => true, 'security' => $this->service->getSecurityOverview($userId)];
    }

    public function setPin(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }
        $pin = (string)($_POST['pin'] ?? '');
        return $this->service->setTransactionPin($userId, $pin);
    }

    public function revokeSession(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }
        $sessionId = (int)($id ?? $_POST['session_id'] ?? 0);
        return $this->service->revokeSession($userId, $sessionId);
    }

    public function toggle2FA(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }
        $enable = (bool)($_POST['enable'] ?? false);
        return $this->service->toggle2FA($userId, $enable);
    }
}
