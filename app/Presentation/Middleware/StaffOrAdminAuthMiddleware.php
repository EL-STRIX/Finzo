<?php
namespace App\Presentation\Middleware;

use App\Shared\Security\Auth;
use App\Shared\Security\SessionManager;

class StaffOrAdminAuthMiddleware
{
    public function handle(): void
    {
        SessionManager::start();
        Auth::requireAdmin(); // This method now explicitly allows both admin and staff.
    }
}
