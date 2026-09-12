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



        $rawHeaders = function_exists('getallheaders') ? getallheaders() : (function_exists('apache_request_headers') ? apache_request_headers() : []);
        $headers = array_change_key_case($rawHeaders, CASE_LOWER);
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $headers['x-csrf-token'] ?? '';
        $token = $_POST['csrf_token'] ?? $headerToken;
        
        // Handle raw input (JSON or url-encoded) if token is not in POST or header
        if (empty($token)) {
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
