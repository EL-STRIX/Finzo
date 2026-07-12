<?php
require_once dirname(__DIR__, 2) . '/../bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/Middleware/auth-staff.php';

use App\Controllers\Staff\StaffController;
use App\Services\StaffService;
use App\Repositories\StaffRepository;

$controller = new StaffController(new StaffService(new StaffRepository()));
$controller->deposit();
