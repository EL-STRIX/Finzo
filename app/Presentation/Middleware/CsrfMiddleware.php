<?php
namespace App\Presentation\Middleware;

use App\Shared\Core\Exceptions\ApiException;

class CsrfMiddleware
{
    public function handle(): void
    {
        // Safe methods that don't change state
        $safeMethods = ['GET', 'HEAD', 'OPTIONS'];
        
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        if (in_array($method, $safeMethods)) {
            return; // No CSRF protection needed for safe methods
        }
        
        // Skip CSRF validation in testing environment, unless explicitly enforced for security tests
        if (defined('FINZO_TESTING') && FINZO_TESTING) {
            if (empty($_SERVER['HTTP_X_ENFORCE_CSRF']) || $_SERVER['HTTP_X_ENFORCE_CSRF'] !== 'true') {
                return;
            }
        }



        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? $headers['X-Csrf-Token'] ?? '';
        $token = $_POST['csrf_token'] ?? $headerToken;
        
        // Handle PUT/DELETE/PATCH raw input
        if (empty($token) && in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (is_array($input) && isset($input['csrf_token'])) {
                $token = $input['csrf_token'];
            } else {
                parse_str($raw, $parsed);
                $token = $parsed['csrf_token'] ?? '';
            }
        }

        if (!\validate_csrf_token($token)) {
            throw new ApiException('Invalid or missing security token (CSRF).', 403);
        }
    }
}
