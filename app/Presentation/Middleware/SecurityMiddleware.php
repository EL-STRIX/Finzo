<?php
namespace App\Presentation\Middleware;

use App\Shared\Security\RateLimiter;

class SecurityMiddleware
{
    public function handle(): void
    {
        // OWASP Security Headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        // X-XSS-Protection is deprecated in modern browsers, but we keep it for legacy client support
        header('X-XSS-Protection: 1; mode=block');
        // Hardened CSP
        header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'none';");
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');

        // Basic CORS (Restrictive)
        header('Access-Control-Allow-Origin: ' . ($_ENV['APP_URL'] ?? '*'));
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        // Skip rate limiting in test environment
        if (defined('FINZO_TESTING') && FINZO_TESTING) {
            return;
        }

        // Global API Rate Limiting (60 requests per minute per IP)
        $ip = get_client_ip();
        RateLimiter::check("global_api_{$ip}", 60, 1);
        
        // Stricter Rate Limiting for Auth Endpoints
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/auth/') !== false) {
            RateLimiter::check("auth_api_{$ip}", 5, 5); // 5 requests per 5 minutes
        }
    }
}