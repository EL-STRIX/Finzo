<?php
/**
 * Finzo Banking System — Update Admin Profile API
 * POST endpoint: allows any admin to update their personal details and password.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/bootstrap/app.php';

use App\Controllers\Admin\AdminManagementController;
use App\Services\AdminManagementService;
use App\Repositories\AdminManagementRepository;

$controller = new AdminManagementController(new AdminManagementService(new AdminManagementRepository()));
$controller->updateAdminProfile();
