<?php
namespace App\Domain\Exceptions;

class InfrastructureException extends DomainException
{
    public function __construct(string $message = "Internal infrastructure error", int $code = 500, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
