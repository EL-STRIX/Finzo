<?php

namespace App\Domain\Contracts\Repositories;

interface CardRepositoryInterface
{
    public function createCardProduct(array $data): int;
    public function getCardProducts(string $status = 'all'): array;
    public function getCardProductById(int $productId);
    
    public function createCardRequest(int $userId, int $accountId, int $productId, string $notes = ''): int;
    public function getCardRequests(array $filters = []): array;
    public function getCardRequestById(int $requestId);
    public function updateCardRequestStatus(int $requestId, string $status): void;
    
    public function issueCard(int $userId, int $accountId, int $productId, string $cardNumber, string $nameOnCard, string $expiryDate, string $cvvHash): int;
    public function getCards(int $userId): array;
    public function getCardById(int $cardId);
    
    public function updateCardStatus(int $cardId, string $status): void;
    public function updateCardPin(int $cardId, string $pinHash): void;
    public function incrementFailedPinAttempts(int $cardId): void;
    public function resetFailedPinAttempts(int $cardId): void;
    
    public function recordCardTransaction(int $cardId, int $accountId, string $type, float $amount, string $referenceId, string $merchant): int;
    public function getCardTransactions(int $cardId): array;
    public function getDailyUsage(int $cardId, string $type): float;
}
