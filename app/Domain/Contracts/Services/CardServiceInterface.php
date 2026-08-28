<?php

namespace App\Domain\Contracts\Services;

interface CardServiceInterface
{
    public function createCardProduct(array $data): array;
    public function getCardProducts(string $status = 'all'): array;
    public function requestCard(int $userId, array $data): array;
    public function getCards(int $userId): array;
    public function getCardRequests(array $filters = []): array;
    public function issueCard(int $requestId, int $adminId, string $notes = ''): array;
    public function rejectCardRequest(int $requestId, int $adminId, string $notes = ''): array;
    
    public function activateCard(int $userId, int $cardId, string $pin): array;
    public function changePin(int $userId, int $cardId, string $oldPin, string $newPin): array;
    public function updateCardStatus(int $userId, int $cardId, string $status): array;
    public function verifyPin(int $userId, int $cardId, string $pin): bool;
    
    public function processCardTransaction(int $userId, int $cardId, string $pin, string $type, float $amount, string $merchant): array;
    public function getCardStatement(int $userId, int $cardId): array;
    public function forceCloseCard(int $cardId, int $adminId, string $reason): array;
}
