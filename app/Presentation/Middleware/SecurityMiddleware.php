<?php
namespace App\Presentation\Middleware;

use App\Shared\Security\RateLimiter;

class SecurityMiddleware
{
    public function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE && getenv('APP_ENV') !== 'testing') {
            session_set_cookie_params([
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
                'lifetime' => 1800 // 30 minutes
            ]);
            session_start();
        }

        // OWASP Security Headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        header('X-XSS-Protection: 1; mode=block');
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none';");
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Basic CORS (Restrictive)
        header('Access-Control-Allow-Origin: ' . ($_ENV['APP_URL'] ?? '*'));
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        // Global API Rate Limiting (60 requests per minute per IP)
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        RateLimiter::check("global_api_{$ip}", 60, 1);
        
        // Stricter Rate Limiting for Auth Endpoints
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/auth/') !== false) {
            RateLimiter::check("auth_api_{$ip}", 5, 5); // 5 requests per 5 minutes
        }
    }
}