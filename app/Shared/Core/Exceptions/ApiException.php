<?php

namespace App\Shared\Core\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected int $statusCode;
    protected array $payload;

    public function __construct(string $message, int $statusCode = 400, array $payload = [])
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->payload = $payload;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
