<?php
/**
 * Finzo Banking System — Get Admin Profile API
 * GET endpoint: returns the logged-in admin's profile and recent activity.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/bootstrap/app.php';

use App\Controllers\Admin\AdminManagementController;
use App\Services\AdminManagementService;
use App\Repositories\AdminManagementRepository;

$controller = new AdminManagementController(new AdminManagementService(new AdminManagementRepository()));
$controller->getAdminProfile();
