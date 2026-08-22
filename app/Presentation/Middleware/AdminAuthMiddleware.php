<?php
namespace App\Presentation\Middleware;

use App\Shared\Security\Auth;
use App\Shared\Security\SessionManager;

class AdminAuthMiddleware
{
    public function handle(): void
    {
        // Start session before any credential check so $_SESSION is populated
        // from the client cookie (required for GET requests which skip CsrfMiddleware).
        SessionManager::start();
        Auth::requireStrictAdmin();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}