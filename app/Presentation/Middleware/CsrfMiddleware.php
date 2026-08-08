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



        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        // Handle PUT/DELETE raw input
        if (empty($token) && in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (is_array($input) && isset($input['csrf_token'])) {
                $token = $input['csrf_token'];
            } else {
                parse_str(file_get_contents('php://input'), $parsed);
                $token = $parsed['csrf_token'] ?? '';
            }
        }

        if (!\validate_csrf_token($token)) {
            throw new ApiException('Invalid or missing security token (CSRF).', 403);
        }
    }
}
