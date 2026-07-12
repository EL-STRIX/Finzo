<?php
/**
 * Finzo Banking System — Get Locked Admins
 * GET endpoint: Returns list of all locked admin accounts.
 */

require_once dirname(__DIR__, 3) . '/bootstrap/app.php';

use App\Controllers\Admin\AdminManagementController;
use App\Services\AdminManagementService;
use App\Repositories\AdminManagementRepository;

$controller = new AdminManagementController(new AdminManagementService(new AdminManagementRepository()));
$controller->getLockedAdmins();
