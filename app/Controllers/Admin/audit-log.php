<?php
/**
 * Finzo Banking System — Admin Audit Log API
 * GET endpoint: paginated audit log entries.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/Middleware/auth-admin.php';
require_once dirname(__DIR__, 3) . '/bootstrap/app.php';

use App\Controllers\Admin\AdminOperationsController;
use App\Services\AdminOperationsService;
use App\Repositories\AdminOperationsRepository;

$controller = new AdminOperationsController(new AdminOperationsService(new AdminOperationsRepository()));
$controller->auditLog();
