<?php

namespace App\Infrastructure\Repositories;

class UserSecurityRepository extends BaseRepository
{
    public function getSecuritySettings(int $userId)
    {
        return $this->fetchOne("SELECT * FROM user_security_settings WHERE user_id = ?", [$userId]);
    }

    public function createOrUpdateSettings(int $userId, array $data): void
    {
        $existing = $this->getSecuritySettings($userId);
        if ($existing) {
            $this->execute("
                UPDATE user_security_settings
                SET two_factor_enabled = ?, two_factor_secret = ?, transaction_pin_hash = COALESCE(?, transaction_pin_hash), login_notifications_enabled = ?, updated_at = NOW()
                WHERE user_id = ?
            ", [
                $data['two_factor_enabled'] ?? 0,
                $data['two_factor_secret'] ?? null,
                $data['transaction_pin_hash'] ?? null,
                $data['login_notifications_enabled'] ?? 1,
                $userId
            ]);
        } else {
            $this->execute("
                INSERT INTO user_security_settings (user_id, two_factor_enabled, two_factor_secret, transaction_pin_hash, login_notifications_enabled, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ", [
                $userId,
                $data['two_factor_enabled'] ?? 0,
                $data['two_factor_secret'] ?? null,
                $data['transaction_pin_hash'] ?? null,
                $data['login_notifications_enabled'] ?? 1
            ]);
        }
    }

    public function setTransactionPin(int $userId, string $pinHash): void
    {
        $existing = $this->getSecuritySettings($userId);
        if ($existing) {
            $this->execute("UPDATE user_security_settings SET transaction_pin_hash = ?, updated_at = NOW() WHERE user_id = ?", [$pinHash, $userId]);
        } else {
            $this->execute("INSERT INTO user_security_settings (user_id, transaction_pin_hash, updated_at) VALUES (?, ?, NOW())", [$userId, $pinHash]);
        }
    }

    public function getActiveSessions(int $userId): array
    {
        return $this->fetchAll("SELECT * FROM user_sessions WHERE user_id = ? ORDER BY last_activity DESC", [$userId]);
    }

    public function recordSession(int $userId, string $token, string $ip, string $userAgent, string $deviceName): void
    {
        $this->execute("
            INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, device_name, last_activity)
            VALUES (?, ?, ?, ?, ?, NOW())
        ", [$userId, $token, $ip, $userAgent, $deviceName]);
    }

    public function revokeSession(int $userId, int $sessionId): void
    {
        $this->execute("DELETE FROM user_sessions WHERE id = ? AND user_id = ?", [$sessionId, $userId]);
    }

    public function revokeOtherSessions(int $userId, string $currentSessionToken): void
    {
        $this->execute("DELETE FROM user_sessions WHERE user_id = ? AND session_token != ?", [$userId, $currentSessionToken]);
    }
}
