<?php
namespace App\Presentation\Middleware;

use App\Shared\Security\Auth;

class StaffAuthMiddleware
{
    public function handle(): void
    {
        Auth::requireStrictStaff();
    }
}
