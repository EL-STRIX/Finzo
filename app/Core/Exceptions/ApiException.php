<?php
namespace App\Core\Exceptions;

use Exception;

class ApiException extends Exception {
    protected int $statusCode;
    protected array $payload;

    public function __construct(string $message, int $statusCode = 400, array $payload = []) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->payload = $payload;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getPayload(): array {
        return $this->payload;
    }
}
