<?php
namespace App\Presentation\Middleware;

use App\Shared\Security\Auth;

class AdminAuthMiddleware
{
    public function handle(): void
    {
        Auth::requireStrictAdmin();
    }
}