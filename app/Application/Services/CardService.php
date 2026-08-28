<?php

namespace App\Application\Services;

use App\Domain\Contracts\Repositories\CardRepositoryInterface;
use App\Domain\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\Contracts\Repositories\AdminOperationsRepositoryInterface;
use App\Domain\Contracts\Services\CardServiceInterface;
use App\Domain\Contracts\Security\SecurityServiceInterface;
use App\Presentation\Validators\CardValidator;
use App\Shared\Core\Exceptions\BusinessRuleException;
use App\Shared\Core\Exceptions\ResourceNotFoundException;
use App\Shared\Core\Database;

class CardService implements CardServiceInterface
{
    private CardRepositoryInterface $repository;
    private UserRepositoryInterface $userRepository;
    private AdminOperationsRepositoryInterface $adminOpsRepository;
    private SecurityServiceInterface $security;
    private CardValidator $validator;

    public function __construct(
        CardRepositoryInterface $repository,
        UserRepositoryInterface $userRepository,
        AdminOperationsRepositoryInterface $adminOpsRepository,
        SecurityServiceInterface $security
    ) {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
        $this->adminOpsRepository = $adminOpsRepository;
        $this->security = $security;
        $this->validator = new CardValidator();
    }

    public function createCardProduct(array $data): array
    {
        $this->validator->validateProductCreation($data);
        
        $productId = $this->repository->createCardProduct($data);
        return ['success' => true, 'message' => 'Card product created successfully', 'product_id' => $productId];
    }

    public function getCardProducts(string $status = 'all'): array
    {
        return $this->repository->getCardProducts($status);
    }

    public function getCards(int $userId): array
    {
        return $this->repository->getCards($userId);
    }

    public function requestCard(int $userId, array $data): array
    {
        $this->validator->validateCardRequest($data);

        $product = $this->repository->getCardProductById($data['card_product_id']);
        if (!$product || $product['status'] !== 'active') {
            throw new ResourceNotFoundException('Active card product not found');
        }

        $user = $this->userRepository->getUserProfile($userId);
        $accounts = $this->userRepository->getUserAccounts($user['customer_id']);
        $savingsAccount = null;
        foreach ($accounts as $acc) {
            if ($acc['account_type'] === 'savings' && $acc['status'] === 'active') {
                $savingsAccount = $acc;
                break;
            }
        }

        if (!$savingsAccount) {
            throw new BusinessRuleException('No active savings account found for card linking');
        }

        // Check if user already has an active or pending card of this product
        $existingCards = $this->repository->getCards($userId);
        foreach ($existingCards as $card) {
            if ($card['card_product_id'] == $product['id'] && in_array($card['status'], ['active', 'pending_activation', 'frozen'])) {
                throw new BusinessRuleException('You already have an active card of this type');
            }
        }
        
        // Check for pending requests
        $existingRequests = $this->repository->getCardRequests(['status' => 'pending']);
        foreach ($existingRequests as $req) {
            if ($req['user_id'] == $userId && $req['card_product_id'] == $product['id']) {
                throw new BusinessRuleException('You already have a pending request for this card type');
            }
        }

        $notes = $this->security->cleanInput($data['notes'] ?? '');
        $requestId = $this->repository->createCardRequest($userId, $savingsAccount['id'], $product['id'], $notes);
        
        return ['success' => true, 'message' => 'Card request submitted successfully', 'request_id' => $requestId];
    }

    public function getCardRequests(array $filters = []): array
    {
        return $this->repository->getCardRequests($filters);
    }

    private function generateUniqueCardNumber(): string
    {
        // Simple generation: starts with 4 (Visa) or 5 (Mastercard), followed by 15 random digits
        // Real systems use IIN/BIN and Luhn algorithm
        // Let's use a 4000 prefix for Visa to simulate
        do {
            $pan = '4000' . rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999);
            // Check uniqueness in DB
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT id FROM cards WHERE card_number = ?");
            $stmt->execute([$pan]);
            $exists = $stmt->fetch();
        } while ($exists);
        
        return (string)$pan;
    }

    public function issueCard(int $requestId, int $adminId, string $notes = ''): array
    {
        $request = $this->repository->getCardRequestById($requestId);
        if (!$request || $request['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Card request not found or not pending'];
        }

        $user = $this->userRepository->getUserProfile($request['user_id']);

        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            $this->repository->updateCardRequestStatus($requestId, 'approved');

            $cardNumber = $this->generateUniqueCardNumber();
            $nameOnCard = strtoupper($user['first_name'] . ' ' . $user['last_name']);
            
            // Expiry 3 years from now
            $expiryDate = date('m/y', strtotime('+3 years'));
            
            // Generate random CVV
            $cvv = (string)rand(100, 999);
            $cvvHash = password_hash($cvv, PASSWORD_BCRYPT);

            $this->repository->issueCard(
                $request['user_id'],
                $request['account_id'],
                $request['card_product_id'],
                $cardNumber,
                $nameOnCard,
                $expiryDate,
                $cvvHash
            );

            // Audit & Notification
            $this->adminOpsRepository->logAudit($adminId, \App\Domain\Enums\AuditActionEnum::USER_STATUS_UPDATE->value, "Issued Card to User {$request['user_id']}");
            $this->adminOpsRepository->addNotification($request['user_id'], 'system', 'Card Issued', "Your new card ending in " . substr($cardNumber, -4) . " has been issued. CVV: {$cvv}. Note: CVV is only shown once here and is hashed securely in our systems. Please keep it safe.");

            $this->repository->commit();
            return ['success' => true, 'message' => 'Card issued successfully'];
        } catch (\Exception $e) {
            $this->repository->rollBack();
            \App\Shared\Core\Logging\Logger::getInstance()->error('Card issuance error: ', ['context' => $e->getMessage()]);
            return ['success' => false, 'message' => 'An error occurred during card issuance'];
        }
    }

    public function rejectCardRequest(int $requestId, int $adminId, string $notes = ''): array
    {
        $request = $this->repository->getCardRequestById($requestId);
        if (!$request || $request['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Card request not found or not pending'];
        }

        $this->repository->updateCardRequestStatus($requestId, 'rejected');
        $this->adminOpsRepository->addNotification($request['user_id'], 'system', 'Card Request Rejected', 'Your recent card request was rejected.');
        
        return ['success' => true, 'message' => 'Card request rejected successfully'];
    }

    public function activateCard(int $userId, int $cardId, string $pin): array
    {
        $this->validator->validatePin(['pin' => $pin]);

        $card = $this->repository->getCardById($cardId);
        if (!$card || (int)$card['user_id'] !== $userId) {
            throw new ResourceNotFoundException('Card not found');
        }

        if ($card['status'] !== 'pending_activation') {
            throw new BusinessRuleException('Card is already activated or cannot be activated');
        }

        $pinHash = password_hash($pin, PASSWORD_BCRYPT);
        
        try {
            $this->repository->beginTransaction();
            
            $this->repository->updateCardPin($cardId, $pinHash);
            $this->repository->updateCardStatus($cardId, 'active');
            
            $this->adminOpsRepository->addNotification($userId, 'system', 'Card Activated', "Your card ending in " . substr($card['card_number'], -4) . " has been successfully activated.");
            
            $this->repository->commit();
            return ['success' => true, 'message' => 'Card activated successfully'];
        } catch (\Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    public function changePin(int $userId, int $cardId, string $oldPin, string $newPin): array
    {
        $this->validator->validatePin(['pin' => $newPin]);

        $card = $this->repository->getCardById($cardId);
        if (!$card || (int)$card['user_id'] !== $userId) {
            throw new ResourceNotFoundException('Card not found');
        }

        if ($card['status'] !== 'active') {
            throw new BusinessRuleException('Card must be active to change PIN');
        }

        if (!password_verify($oldPin, $card['pin_hash'])) {
            $this->repository->incrementFailedPinAttempts($cardId);
            throw new BusinessRuleException('Incorrect old PIN');
        }

        // Reset attempts on success
        $this->repository->resetFailedPinAttempts($cardId);
        $pinHash = password_hash($newPin, PASSWORD_BCRYPT);
        $this->repository->updateCardPin($cardId, $pinHash);
        
        $this->adminOpsRepository->addNotification($userId, 'security', 'Card PIN Changed', "The PIN for your card ending in " . substr($card['card_number'], -4) . " was recently changed.");
        
        return ['success' => true, 'message' => 'PIN changed successfully'];
    }

    public function updateCardStatus(int $userId, int $cardId, string $status): array
    {
        $card = $this->repository->getCardById($cardId);
        if (!$card || (int)$card['user_id'] !== $userId) {
            throw new ResourceNotFoundException('Card not found');
        }

        $allowedTransitions = [
            'active' => ['frozen', 'closed', 'blocked'], // Customer can freeze or report lost (closed/blocked)
            'frozen' => ['active', 'closed', 'blocked']
        ];

        if (!isset($allowedTransitions[$card['status']]) || !in_array($status, $allowedTransitions[$card['status']])) {
            throw new BusinessRuleException("Cannot transition card from {$card['status']} to {$status}");
        }

        $this->repository->updateCardStatus($cardId, $status);
        $this->adminOpsRepository->addNotification($userId, 'security', 'Card Status Updated', "Your card ending in " . substr($card['card_number'], -4) . " status is now: {$status}.");
        
        return ['success' => true, 'message' => 'Card status updated successfully'];
    }

    public function verifyPin(int $userId, int $cardId, string $pin): bool
    {
        $card = $this->repository->getCardById($cardId);
        if (!$card || (int)$card['user_id'] !== $userId) {
            return false;
        }

        if ($card['status'] !== 'active') {
            return false;
        }

        if ($card['failed_pin_attempts'] >= 3) {
            // Already blocked, but maybe status wasn't updated yet?
            if ($card['status'] !== 'blocked') {
                $this->repository->updateCardStatus($cardId, 'blocked');
                $this->adminOpsRepository->addNotification($userId, 'security', 'Card Blocked', "Your card ending in " . substr($card['card_number'], -4) . " has been blocked due to multiple failed PIN attempts.");
            }
            return false;
        }

        if (password_verify($pin, $card['pin_hash'])) {
            $this->repository->resetFailedPinAttempts($cardId);
            return true;
        } else {
            $this->repository->incrementFailedPinAttempts($cardId);
            $card = $this->repository->getCardById($cardId); // reload to get updated count
            if ($card['failed_pin_attempts'] >= 3) {
                $this->repository->updateCardStatus($cardId, 'blocked');
                $this->adminOpsRepository->addNotification($userId, 'security', 'Card Blocked', "Your card ending in " . substr($card['card_number'], -4) . " has been blocked due to multiple failed PIN attempts.");
            }
            return false;
        }
    }

    public function processCardTransaction(int $userId, int $cardId, string $pin, string $type, float $amount, string $merchant): array
    {
        if ($amount <= 0) {
            throw new BusinessRuleException('Amount must be greater than zero');
        }

        if (!in_array($type, ['pos', 'atm', 'online'])) {
            throw new BusinessRuleException('Invalid transaction type');
        }

        // 1. Verify PIN and Status (verifyPin checks status = active and increments attempts on failure)
        if (!$this->verifyPin($userId, $cardId, $pin)) {
            throw new BusinessRuleException('Invalid PIN or card is blocked');
        }

        $card = $this->repository->getCardById($cardId); // reload for full details
        
        // 2. Check Daily Limits
        $dailyUsage = $this->repository->getDailyUsage($cardId, $type);
        $limitField = ($type === 'atm') ? 'daily_withdrawal_limit' : 'daily_purchase_limit';
        $limit = (float)$card[$limitField];
        
        if (($dailyUsage + $amount) > $limit) {
            throw new BusinessRuleException("Transaction exceeds the daily limit of ₹" . number_format($limit, 2) . " for this card type");
        }

        try {
            $this->repository->beginTransaction();

            // 3. Lock the linked account
            $lockedAcc = $this->adminOpsRepository->getAccountForUpdate($card['account_id']);
            
            // 4. Ensure sufficient balance
            if ((float)$lockedAcc['balance'] < $amount) {
                throw new BusinessRuleException('Insufficient funds in the linked account for this card transaction');
            }

            $newBalance = (float)$lockedAcc['balance'] - $amount;
            
            // 5. Deduct funds via AdminOpsRepository (to track in generic ledger)
            $txnId = 'CARD' . strtoupper(bin2hex(random_bytes(6)));
            $description = "Card $type at $merchant";
            
            $this->adminOpsRepository->addTransaction(
                $txnId,
                $lockedAcc['id'],
                null, // no receiver
                'withdrawal',
                $amount,
                $newBalance,
                $description,
                "Card ending in " . substr($card['card_number'], -4),
                'completed'
            );
            $this->adminOpsRepository->updateAccountBalance($newBalance, $lockedAcc['id']);

            // 6. Record in card_transactions
            $cardTxnId = $this->repository->recordCardTransaction($cardId, $card['account_id'], $type, $amount, $txnId, $merchant);

            $this->repository->commit();

            // Notify
            $this->adminOpsRepository->addNotification($userId, 'transaction', 'Card Swiped', "₹" . number_format($amount, 2) . " spent at $merchant using your card ending in " . substr($card['card_number'], -4));

            return ['success' => true, 'message' => 'Transaction approved', 'transaction_id' => $txnId];
        } catch (\Exception $e) {
            $this->repository->rollBack();
            \App\Shared\Core\Logging\Logger::getInstance()->error('Card transaction error: ', ['context' => $e->getMessage()]);
            throw new BusinessRuleException('Transaction failed due to an internal error');
        }
    }

    public function getCardStatement(int $userId, int $cardId): array
    {
        $card = $this->repository->getCardById($cardId);
        if (!$card || (int)$card['user_id'] !== $userId) {
            throw new ResourceNotFoundException('Card not found');
        }

        return $this->repository->getCardTransactions($cardId);
    }

    public function forceCloseCard(int $cardId, int $adminId, string $reason): array
    {
        $card = $this->repository->getCardById($cardId);
        if (!$card) {
            throw new ResourceNotFoundException('Card not found');
        }

        if ($card['status'] === 'closed') {
            throw new BusinessRuleException('Card is already closed');
        }

        $this->repository->updateCardStatus($cardId, 'closed');
        
        $this->adminOpsRepository->logAudit($adminId, \App\Domain\Enums\AuditActionEnum::USER_STATUS_UPDATE->value, "Force closed card $cardId. Reason: $reason");
        $this->adminOpsRepository->addNotification($card['user_id'], 'system', 'Card Force Closed', "Your card ending in " . substr($card['card_number'], -4) . " has been permanently closed by an administrator. Reason: $reason");
        
        return ['success' => true, 'message' => 'Card force closed successfully'];
    }
}
