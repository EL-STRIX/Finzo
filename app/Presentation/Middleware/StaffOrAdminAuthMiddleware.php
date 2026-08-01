<?php
namespace App\Presentation\Middleware;

use App\Shared\Security\Auth;

class StaffOrAdminAuthMiddleware
{
    public function handle(): void
    {
        Auth::requireAdmin(); // This method now explicitly allows both admin and staff.
    }
}
