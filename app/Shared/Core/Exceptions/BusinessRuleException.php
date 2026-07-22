<?php
namespace App\Shared\Core\Exceptions;

class BusinessRuleException extends \Exception
{
    protected array $payload = [];

    public function __construct(string $message, array $payload = [])
    {
        parent::__construct($message, 400);
        $this->payload = $payload;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}