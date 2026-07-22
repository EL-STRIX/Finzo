<?php
namespace App\Shared\Core\Exceptions;

class ConflictException extends \Exception
{
    protected array $payload = [];

    public function __construct(string $message, array $payload = [])
    {
        parent::__construct($message, 409);
        $this->payload = $payload;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}