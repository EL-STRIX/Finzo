<?php
namespace App\Shared\Core\Exceptions;

use App\Shared\Core\Http\ResponseBuilder;
use App\Shared\Core\Logging\Logger;

class ExceptionHandler
{
    public static function handle(\Throwable $e): void
    {
        $status = 500;
        $payload = ['message' => 'Internal Server Error'];

        if ($e instanceof ValidationException) {
            $status = 422;
            $payload = ['message' => $e->getMessage(), 'errors' => $e->getPayload()['errors'] ?? []];
        } elseif ($e instanceof AuthenticationException) {
            $status = 401;
            $payload = ['message' => $e->getMessage()];
        } elseif ($e instanceof AuthorizationException) {
            $status = 403;
            $payload = ['message' => $e->getMessage()];
        } elseif ($e instanceof ResourceNotFoundException) {
            $status = 404;
            $payload = ['message' => $e->getMessage()];
        } elseif ($e instanceof ConflictException) {
            $status = 409;
            $payload = ['message' => $e->getMessage()];
        } elseif ($e instanceof BusinessRuleException) {
            $status = 400;
            $payload = ['message' => $e->getMessage()];
        } elseif ($e instanceof ApiException) {
            $status = $e->getStatusCode();
            $payload = array_merge(['message' => $e->getMessage()], $e->getPayload());
        }

        // Centralized logging with full context
        $logContext = [
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $e->getTraceAsString(),
            'uri'       => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method'    => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        ];

        if ($status >= 500) {
            Logger::getInstance()->critical('Unhandled exception', $logContext);
        } elseif ($status >= 400) {
            Logger::getInstance()->warning('Client error', $logContext);
        }

        $formatted = ResponseBuilder::format($payload, $status);

        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($formatted, JSON_UNESCAPED_UNICODE);
        
        if (getenv('APP_ENV') !== 'testing') {
            exit;
        }
    }
}