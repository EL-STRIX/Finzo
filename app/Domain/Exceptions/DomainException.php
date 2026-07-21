<?php
namespace App\Domain\Exceptions;

use Exception;

class DomainException extends Exception
{
    protected $payload = [];

    public function __construct(string $message = "", int $code = 400, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->code ?: 400;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): self
    {
        $this->payload = $payload;
        return $this;
    }
}
