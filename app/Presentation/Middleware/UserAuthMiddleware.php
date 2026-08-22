<?php
namespace App\Presentation\Middleware;

use App\Shared\Security\Auth;
use App\Shared\Security\SessionManager;

class UserAuthMiddleware
{
    public function handle(): void
    {
        SessionManager::start();
        Auth::requireCustomer();
    }
}