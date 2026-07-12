<?php
/**
 * Finzo Banking System — Admin Management Actions API
 * POST endpoint: Suspend, Activate, Delete, or Reset Password for an admin.
 * Requires Super Admin privileges.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/bootstrap/app.php';

use App\Controllers\Admin\AdminManagementController;
use App\Services\AdminManagementService;
use App\Repositories\AdminManagementRepository;

$controller = new AdminManagementController(new AdminManagementService(new AdminManagementRepository()));
$controller->adminAction();
