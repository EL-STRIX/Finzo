<?php
/**
 * Finzo Banking System — Admin Interest Rate Management API
 * GET: fetch current rate and history | POST: update rate
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-admin.php';
require_once dirname(__DIR__, 3) . '/bootstrap/app.php';

use App\Controllers\Admin\AdminOperationsController;
use App\Services\AdminOperationsService;
use App\Repositories\AdminOperationsRepository;

$controller = new AdminOperationsController(new AdminOperationsService(new AdminOperationsRepository()));
$controller->interest();
