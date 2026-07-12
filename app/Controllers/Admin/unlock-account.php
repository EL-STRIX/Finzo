<?php
require_once dirname(__DIR__, 2) . '/../bootstrap/app.php';
use App\Controllers\Admin\AdminOperationsController;
use App\Services\AdminOperationsService;
use App\Repositories\AdminOperationsRepository;

$controller = new AdminOperationsController(new AdminOperationsService(new AdminOperationsRepository()));
$controller->unlockAccount();
