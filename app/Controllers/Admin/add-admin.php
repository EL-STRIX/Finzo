<?php
/**
 * Finzo Banking System — Add Admin API
 * POST endpoint: allows super_admin to create new admins.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/bootstrap/app.php';

use App\Controllers\Admin\AdminManagementController;
use App\Services\AdminManagementService;
use App\Repositories\AdminManagementRepository;

$controller = new AdminManagementController(new AdminManagementService(new AdminManagementRepository()));
$controller->addAdmin();
