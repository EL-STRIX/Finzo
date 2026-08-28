<?php

namespace App\Presentation\Api\V1\User;

use App\Domain\Contracts\Services\CardServiceInterface;
use App\Domain\Contracts\Security\SecurityServiceInterface;
use App\Shared\Core\Exceptions\ApiException;

class UserCardController
{
    private CardServiceInterface $service;
    private SecurityServiceInterface $security;

    public function __construct(CardServiceInterface $service, ?SecurityServiceInterface $security = null)
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
        $cards = $this->service->getCards($userId);
        return ['success' => true, 'cards' => $cards];
    }

    public function products(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $products = $this->service->getCardProducts('active');
        return ['success' => true, 'products' => $products];
    }

    public function requestCard(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            throw new ApiException('Unauthorized', 401);
        }

        $data = [
            'card_product_id' => $_POST['card_product_id'] ?? '',
            'notes' => $this->security->cleanInput($_POST['notes'] ?? '')
        ];

        return $this->service->requestCard($userId, $data);
    }

    public function activate(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        $cardId = (int)($id ?? $_POST['card_id'] ?? 0);
        $pin = (string)($_POST['pin'] ?? '');

        return $this->service->activateCard($userId, $cardId, $pin);
    }

    public function changePin(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        $cardId = (int)($id ?? $_POST['card_id'] ?? 0);
        $oldPin = (string)($_POST['old_pin'] ?? '');
        $newPin = (string)($_POST['new_pin'] ?? '');

        return $this->service->changePin($userId, $cardId, $oldPin, $newPin);
    }

    public function updateStatus(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        $cardId = (int)($id ?? $_POST['card_id'] ?? 0);
        $status = $this->security->cleanInput($_POST['status'] ?? '');

        return $this->service->updateCardStatus($userId, $cardId, $status);
    }

    public function transact(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        $cardId = (int)($id ?? $_POST['card_id'] ?? 0);
        $pin = (string)($_POST['pin'] ?? '');
        $type = $this->security->cleanInput($_POST['type'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $merchant = $this->security->cleanInput($_POST['merchant'] ?? 'Unknown');

        return $this->service->processCardTransaction($userId, $cardId, $pin, $type, $amount, $merchant);
    }

    public function statement(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        $cardId = (int)($id ?? $_GET['card_id'] ?? 0);

        $transactions = $this->service->getCardStatement($userId, $cardId);
        return ['success' => true, 'transactions' => $transactions];
    }
}
