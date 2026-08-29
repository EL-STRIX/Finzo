<?php

namespace App\Presentation\Api\V1\Admin;

use App\Domain\Contracts\Services\CardServiceInterface;
use App\Domain\Contracts\Security\SecurityServiceInterface;
use App\Shared\Core\Exceptions\ApiException;

class AdminCardController
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

    public function products(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $status = $this->security->cleanInput($_GET['status'] ?? 'all');
        $products = $this->service->getCardProducts($status !== '' ? $status : 'all');

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
            'type' => $_POST['type'] ?? '',
            'network' => $_POST['network'] ?? '',
            'daily_withdrawal_limit' => $_POST['daily_withdrawal_limit'] ?? '',
            'daily_purchase_limit' => $_POST['daily_purchase_limit'] ?? '',
        ];

        return $this->service->createCardProduct($data);
    }

    public function requests(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $status = $_GET['status'] ?? 'all';
        $requests = $this->service->getCardRequests(['status' => $status]);
        return ['success' => true, 'requests' => $requests];
    }

    public function issueCard(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $requestId = (int)($id ?? $_POST['id'] ?? 0);
        $adminId = $_SESSION['admin_id'] ?? 0;
        $notes = $this->security->cleanInput($_POST['notes'] ?? '');
        
        return $this->service->issueCard($requestId, $adminId, $notes);
    }

    public function rejectRequest(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $requestId = (int)($id ?? $_POST['id'] ?? 0);
        $adminId = $_SESSION['admin_id'] ?? 0;
        $notes = $this->security->cleanInput($_POST['notes'] ?? '');
        
        return $this->service->rejectCardRequest($requestId, $adminId, $notes);
    }

    public function forceClose(?string $id = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ApiException('Method Not Allowed', 405);
        }
        $cardId = (int)($id ?? $_POST['card_id'] ?? 0);
        $adminId = $_SESSION['admin_id'] ?? 0;
        $reason = $this->security->cleanInput($_POST['reason'] ?? 'Administrative Action');
        
        return $this->service->forceCloseCard($cardId, $adminId, $reason);
    }
}
