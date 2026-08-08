<?php

namespace App\Domain\Contracts\Services;

interface NotificationServiceInterface
{
    /**
     * Send a notification to a user.
     * 
     * @param string $userId
     * @param string $type (transaction, security, system)
     * @param string $title
     * @param string $message
     * @return bool
     */
    public function send(string $userId, string $type, string $title, string $message): bool;
}
