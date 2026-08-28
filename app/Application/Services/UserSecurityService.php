<?php

namespace App\Application\Services;

use App\Infrastructure\Repositories\UserSecurityRepository;
use App\Shared\Core\Exceptions\ApiException;

class UserSecurityService
{
    private UserSecurityRepository $repository;

    public function __construct(UserSecurityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getSecurityOverview(int $userId): array
    {
        $settings = $this->repository->getSecuritySettings($userId);
        $sessions = $this->repository->getActiveSessions($userId);

        // If no active sessions recorded yet, add current
        if (empty($sessions)) {
            $ip = get_client_ip();
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Browser';
            $token = session_id();
            $this->repository->recordSession($userId, $token, $ip, $agent, 'Current Browser Session');
            $sessions = $this->repository->getActiveSessions($userId);
        }

        return [
            'two_factor_enabled' => (bool)($settings['two_factor_enabled'] ?? false),
            'has_transaction_pin' => !empty($settings['transaction_pin_hash']),
            'login_notifications_enabled' => (bool)($settings['login_notifications_enabled'] ?? true),
            'sessions' => $sessions
        ];
    }

    public function setTransactionPin(int $userId, string $pin): array
    {
        if (strlen($pin) !== 4 || !ctype_digit($pin)) {
            throw new ApiException('Transaction PIN must be exactly 4 numeric digits.', 400);
        }
        $pinHash = password_hash($pin, PASSWORD_BCRYPT);
        $this->repository->setTransactionPin($userId, $pinHash);

        return ['success' => true, 'message' => '4-digit Transaction PIN set successfully!'];
    }

    public function revokeSession(int $userId, int $sessionId): array
    {
        $this->repository->revokeSession($userId, $sessionId);
        return ['success' => true, 'message' => 'Session revoked successfully.'];
    }

    public function toggle2FA(int $userId, bool $enable): array
    {
        $settings = $this->repository->getSecuritySettings($userId) ?: [];
        $secret = $settings['two_factor_secret'] ?? bin2hex(random_bytes(10));

        $this->repository->createOrUpdateSettings($userId, [
            'two_factor_enabled' => $enable ? 1 : 0,
            'two_factor_secret' => $secret,
            'transaction_pin_hash' => $settings['transaction_pin_hash'] ?? null,
            'login_notifications_enabled' => $settings['login_notifications_enabled'] ?? 1
        ]);

        return [
            'success' => true,
            'two_factor_enabled' => $enable,
            'secret' => $secret,
            'message' => $enable ? '2FA Authenticator activated!' : '2FA disabled.'
        ];
    }
}
