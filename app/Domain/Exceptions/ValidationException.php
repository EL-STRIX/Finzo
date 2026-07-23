<?php
namespace App\Domain\Exceptions;

class ValidationException extends DomainException
{
    public function __construct(string $message = "Validation failed", int $code = 422, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
