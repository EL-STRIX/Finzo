<?php

require_once dirname(__DIR__, 3) . '/../bootstrap/app.php';
require_once dirname(__DIR__, 3) . '/Presentation/Middleware/auth-staff.php';

use App\Presentation\Controllers\Staff\StaffController;
use App\Application\Services\StaffService;
use App\Infrastructure\Repositories\StaffRepository;

$controller = new StaffController(new StaffService(new StaffRepository()));
$controller->changePassword();
