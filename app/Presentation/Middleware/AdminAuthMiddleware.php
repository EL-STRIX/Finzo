<?php
namespace App\Presentation\Middleware;

use App\Shared\Security\Auth;

class AdminAuthMiddleware
{
    public function handle(): void
    {
        Auth::requireStrictAdmin();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}