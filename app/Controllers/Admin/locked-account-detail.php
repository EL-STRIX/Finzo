<?php
/**
 * Finzo Banking System — Admin Locked Account Detail API
 * GET endpoint: full locked-account profile with audit logs.
 * Version 1.1 — May 2026
 */

require_once dirname(__DIR__, 2) . '/Middleware/auth-admin.php';
require_once dirname(__DIR__, 3) . '/bootstrap/app.php';

use App\Controllers\Admin\AdminOperationsController;
use App\Services\AdminOperationsService;
use App\Repositories\AdminOperationsRepository;

$controller = new AdminOperationsController(new AdminOperationsService(new AdminOperationsRepository()));
$controller->lockedAccountDetail();
