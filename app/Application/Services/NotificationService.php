<?php

namespace App\Application\Services;

use App\Domain\Contracts\Services\NotificationServiceInterface;
use App\Shared\Core\Database;

class NotificationService implements NotificationServiceInterface
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function send(string $userId, string $type, string $title, string $message): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, type, title, message)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$userId, $type, $title, $message]);
    }
}
