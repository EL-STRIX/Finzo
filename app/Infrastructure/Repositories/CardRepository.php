<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\Repositories\CardRepositoryInterface;
use App\Shared\Core\Database;

class CardRepository extends BaseRepository implements CardRepositoryInterface
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function execute(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        return $this->execute($sql, $params)->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchOne(string $sql, array $params = [])
    {
        $result = $this->execute($sql, $params)->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function createCardProduct(array $data): int
    {
        $this->execute("
            INSERT INTO card_products (name, type, network, daily_withdrawal_limit, daily_purchase_limit, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'active', NOW())
        ", [
            $data['name'], 
            $data['type'], 
            $data['network'], 
            $data['daily_withdrawal_limit'], 
            $data['daily_purchase_limit']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getCardProducts(string $status = 'all'): array
    {
        $sql = "SELECT * FROM card_products";
        $params = [];
        if ($status !== 'all') {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        return $this->fetchAll($sql, $params);
    }

    public function getCardProductById(int $productId)
    {
        return $this->fetchOne("SELECT * FROM card_products WHERE id = ?", [$productId]);
    }

    public function createCardRequest(int $userId, int $accountId, int $productId, string $notes = ''): int
    {
        $this->execute("
            INSERT INTO card_requests (user_id, account_id, card_product_id, status, notes, created_at)
            VALUES (?, ?, ?, 'pending', ?, NOW())
        ", [$userId, $accountId, $productId, $notes]);
        return (int)$this->db->lastInsertId();
    }

    public function getCardRequests(array $filters = []): array
    {
        $sql = "SELECT cr.*, u.first_name, u.last_name, cp.name as product_name, cp.type as product_type 
                FROM card_requests cr
                JOIN users u ON cr.user_id = u.id
                JOIN card_products cp ON cr.card_product_id = cp.id";
        $params = [];
        
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " WHERE cr.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY cr.created_at DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getCardRequestById(int $requestId)
    {
        return $this->fetchOne("SELECT * FROM card_requests WHERE id = ?", [$requestId]);
    }

    public function updateCardRequestStatus(int $requestId, string $status): void
    {
        $this->execute("UPDATE card_requests SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $requestId]);
    }

    public function issueCard(int $userId, int $accountId, int $productId, string $cardNumber, string $nameOnCard, string $expiryDate, string $cvvHash): int
    {
        $this->execute("
            INSERT INTO cards (card_number, user_id, account_id, card_product_id, name_on_card, expiry_date, cvv_hash, status, issued_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending_activation', NOW())
        ", [$cardNumber, $userId, $accountId, $productId, $nameOnCard, $expiryDate, $cvvHash]);
        return (int)$this->db->lastInsertId();
    }

    public function getCards(int $userId): array
    {
        return $this->fetchAll("
            SELECT c.id, c.card_number, c.name_on_card, c.expiry_date, c.status, cp.name as product_name, cp.network, cp.type
            FROM cards c
            JOIN card_products cp ON c.card_product_id = cp.id
            WHERE c.user_id = ?
            ORDER BY c.issued_at DESC
        ", [$userId]);
    }

    public function getCardById(int $cardId)
    {
        return $this->fetchOne("
            SELECT c.*, cp.name as product_name, cp.network, cp.type, cp.daily_withdrawal_limit, cp.daily_purchase_limit
            FROM cards c
            JOIN card_products cp ON c.card_product_id = cp.id
            WHERE c.id = ?
        ", [$cardId]);
    }

    public function updateCardStatus(int $cardId, string $status): void
    {
        $this->execute("UPDATE cards SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $cardId]);
    }

    public function updateCardPin(int $cardId, string $pinHash): void
    {
        $this->execute("UPDATE cards SET pin_hash = ?, activated_at = IF(activated_at IS NULL, NOW(), activated_at), updated_at = NOW() WHERE id = ?", [$pinHash, $cardId]);
    }

    public function incrementFailedPinAttempts(int $cardId): void
    {
        $this->execute("UPDATE cards SET failed_pin_attempts = failed_pin_attempts + 1, updated_at = NOW() WHERE id = ?", [$cardId]);
    }

    public function resetFailedPinAttempts(int $cardId): void
    {
        $this->execute("UPDATE cards SET failed_pin_attempts = 0, updated_at = NOW() WHERE id = ?", [$cardId]);
    }

    public function recordCardTransaction(int $cardId, int $accountId, string $type, float $amount, string $referenceId, string $merchant): int
    {
        $this->execute("
            INSERT INTO card_transactions (card_id, account_id, transaction_type, amount, status, reference_id, merchant_name, created_at)
            VALUES (?, ?, ?, ?, 'completed', ?, ?, NOW())
        ", [$cardId, $accountId, $type, $amount, $referenceId, $merchant]);
        
        return (int)$this->db->lastInsertId();
    }

    public function getCardTransactions(int $cardId): array
    {
        return $this->fetchAll("
            SELECT * FROM card_transactions 
            WHERE card_id = ? 
            ORDER BY created_at DESC
        ", [$cardId]);
    }

    public function getDailyUsage(int $cardId, string $type): float
    {
        // $type should be 'atm' or 'pos' (or online mapped to pos)
        $result = $this->fetchOne("
            SELECT SUM(amount) as daily_total 
            FROM card_transactions 
            WHERE card_id = ? 
              AND transaction_type = ? 
              AND status = 'completed'
              AND DATE(created_at) = CURDATE()
        ", [$cardId, $type]);
        
        return (float)($result['daily_total'] ?? 0);
    }
}
