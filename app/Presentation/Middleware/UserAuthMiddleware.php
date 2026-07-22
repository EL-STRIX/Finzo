<?php
namespace App\Presentation\Middleware;

use App\Shared\Security\Auth;

class UserAuthMiddleware
{
    public function handle(): void
    {
        Auth::requireCustomer();
    }
}