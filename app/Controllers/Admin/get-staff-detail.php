<?php
require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/Middleware/auth-admin.php';

use App\Controllers\Admin\AdminStaffBranchController;
use App\Services\AdminStaffBranchService;
use App\Repositories\AdminStaffBranchRepository;

$controller = new AdminStaffBranchController(new AdminStaffBranchService(new AdminStaffBranchRepository()));
$controller->getStaffDetail();
